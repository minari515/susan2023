<?php
ini_set('display_errors',1);

require_once(dirname(__FILE__)."/../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__."/../../../"); //.envを読み込む
$dotenv->load();

class DiscussionsController
{
  public $code = 200;
  public $url;
  public $request_body;

  function __construct()
  {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  /**************************************************************************** */
  /**
   * GETメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function get($args) {
    $questionIndex = $args[0];
    if($questionIndex == null) {
      $this->code = 400;
      return array("error" => "questionIndex is required");
    }
    return $this->getDiscussion($questionIndex);
  }

  /**
   * 質疑応答におけるスレッド内の会話を取得
   * @param int $questionIndex 質疑応答のインデックス
   * @return array スレッドの会話情報
   */
  private function getDiscussion($questionIndex) {
    $db = new DB();

    try{
      // mysqlの実行文(各LINEid毎の最新メッセージを取得)
      $stmt = $db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`userType`,`isQuestionersMessage`,`messageType`,`message`
        FROM `Discussions` 
        WHERE questionIndex = :questionIndex
        ORDER BY `Discussions`.`index`  ASC"
      );
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }else{
        $this -> code = 500;
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $this -> code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }


  /**************************************************************************** */
  /**
   * POSTメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function post($args) {
    $messageType = $args[0];
    if($messageType == null) {
      $this -> code = 400;
      return ["error" => [
        "type" => "invalid_access"
      ]];
    }

    if($messageType == "chat" || $messageType == "answer"){
      $post = $this->request_body;
    }else if($messageType == "image"){
      $post = $_POST;
    }
    // 必須パラメータの確認
    if(!array_key_exists("index",$post) || 
      !array_key_exists("userIdToken",$post) ||
      !array_key_exists("isUsersQuestion",$post)
    ){
      $this->code = 400;
      return ["error" => [
        "type" => "invalid_param",
        "message" => "index and token are required"
      ]];
    }

    // ユーザーの存在確認
    include("users.php");
    $usersController = new UsersController();
    try{
      $userId = $usersController->verifyLine($post["userIdToken"])["sub"];
      $userType = $usersController->getUserInfo($userId)["response"]["type"];
    }catch(Exception $error){
      $result = json_decode($error->getMessage(),true);
      $this->code = $error["status"];
      return $result["error"];
    }

    switch($messageType) {
      // スレッドに新規テキストメッセージを追加
      case "chat":
      case "answer":
        if(!array_key_exists("message",$post)){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        $insertResponse = $this->setDiscussionMessage($post["index"], $userId, $userType, $post["isUsersQuestion"], $messageType, $post["message"]);
        break;
      
      // スレッドに画像を追加
      case "image":
        if (!isset($_FILES['file']['error']) || !is_int($_FILES['file']['error'])) {
          // 未定義である・複数ファイルである・$_FILES Corruption 攻撃を受けた
          // どれかに該当していれば不正なパラメータとして処理する
          $this->code = 412;
          return ["error" => [
            "type" => "invalid_file"
          ]];
        }
        $response = $this->saveImageFile($_FILES);
        if(!array_key_exists('fileName',$response)){
          return $response;
        }
        $isUsersQuestion = $post["isUsersQuestion"] == "true" ? true : false;

        $insertResponse = $this->setDiscussionMessage($post["index"], $userId, $userType, $isUsersQuestion, $messageType, $response['fileName']);
        break;

      // 無効なアクセス
      default:
        // 多分ここには来ない
        $this -> code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
        break;
    }
    if(array_key_exists("error",$insertResponse)){
      $this->code = 500;
      return $insertResponse;
    }
    $questioner = $usersController->getQuestionerLineId($post["index"])["lineId"];
    $assignees = $usersController->getAssignedStudent($post["index"])["assigneeIds"];
    $assignees[] = $questioner;
    return [
      "insertedMessage" => $insertResponse,
      "pushTo" => $assignees,
    ];
  }

  /**
   * 質疑応答のスレッドにメッセージを追加する
   * @param int $questionIndex 質問ID
   * @param string $userId 投稿者のLINE ID
   * @param string $userType 投稿者の種類
   * @param string $messageType chat/answer
   * @param string $message
   */
  private function setDiscussionMessage($questionIndex, $userId, $userType, $isUsersQuestion, $messageType, $message) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      $stmt = $pdo -> prepare(
        "INSERT INTO Discussions (questionIndex, userId, userType, isQuestionersMessage, messageType, message)
        VALUES (
          :questionIndex,
          :userId,
          :userType,
          (SELECT COUNT(*) FROM `Questions` WHERE `index`=:qIndex AND `questionerId` = :questionerId LIMIT 1),
          :messageType, 
          :message
        )"
      );
      //データの紐付け
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
      $stmt->bindValue(':userType', $userType, PDO::PARAM_STR);
      $stmt->bindValue(':qIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':questionerId', $userId, PDO::PARAM_STR);
      $stmt->bindValue(':messageType', $messageType, PDO::PARAM_STR);
      $stmt->bindValue(':message', $message, PDO::PARAM_STR);
      
      // 実行
      $res = $stmt->execute();
      $lastIndex = $pdo->lastInsertId();
      $timestamp = date("Y-m-d H:i:s");
      if($res){
        $this->code = 201;
        //header("Location: ".$this->url.$lastIndex);
        include(dirname( __FILE__)."/../utils/sendEmail.php");
        if($messageType == "image"){
          sendEmailToInstructors("message", "画像が投稿されました", $questionIndex);
        }else{
          sendEmailToInstructors("message", $message, $questionIndex);
        }
        return [
          "index" => $lastIndex,
          "timestamp" => $timestamp,
          "userType" => $userType,
          "isQuestionersMessage" => $isUsersQuestion,
          "messageType" => $messageType,
          "message" => $message
        ];
      }else{
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $this -> code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }

  private function saveImageFile($postedFiles){
    // バリデーション
    try {
      // $_FILES['upfile']['error'] の値を確認
      switch ($postedFiles['file']['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択
          throw new RuntimeException('No_file_selected');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過 (設定した場合のみ)
          throw new RuntimeException('File_size_too_large');
        default:
          throw new RuntimeException('Unexpected_Error');
      }

      // ここで定義するサイズ上限のオーバーチェック(50MB)
      // (必要がある場合のみ)
      if ($postedFiles['file']['size'] > 50000000) {
        throw new RuntimeException('File_size_too_large');
      }

      // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので
      // MIMEタイプに対応する拡張子を自前で取得する
      if (!$extension = array_search(
        mime_content_type($postedFiles['file']['tmp_name']),
        array(
          'gif' => 'image/gif',
          'jpg' => 'image/jpeg',
          'png' => 'image/png',
        ),
        true
      )) {
        throw new RuntimeException('Incorrect_file_format.');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し保存する(ディレクトリ・トラバーサル対策)
      $homeDir = getenv('HOME_DIR');
      if (!move_uploaded_file(
        $postedFiles['file']['tmp_name'],
        $path = sprintf($homeDir.'/upload_images/%s.%s',
          sha1_file($postedFiles['file']['tmp_name']),
          $extension
        )
      )) {
        throw new RuntimeException('An_error_occurred_while_saving_the_file');
      }

      // ファイルのパーミッションを確実に0644に設定する
      chmod($path, 0644);

      $this->code = 201;
      $dirNames = explode("/", $homeDir);
      // "/home/ユーザー名/public_html/upload_images/ファイル名.拡張子" → "/~ユーザー名/upload_images/ファイル名.拡張子"
      return ["fileName" => preg_replace("/\/".$dirNames[1]."\/".$dirNames[2]."\/".$dirNames[3]."/", "/~".$dirNames[2], $path, 1)];

    } catch (RuntimeException $e) {
      $this -> code = 412;
      return ["error" => [
        "type" => "RuntimeException",
        "message" => $e->getMessage()
      ]];
    }
  }
  /**************************************************************************** */
  public function options()
  {
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST");
    header("Access-Control-Allow-Headers: Content-Type");
    return [];
  }

  private function is_set($value)
  {
    return !(is_null($value) || $value === "");
  }
}