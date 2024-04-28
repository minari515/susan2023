<?php
// ini_set('display_errors',1);

require(dirname( __FILE__)."../../../../app_service/QuestionsAppService.php");

class QuestionsController
{
  public $code = 200;
  public $url;
  public $request_body;

  private $questionsAppService;

  function __construct()
  {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);

    $this->questionsAppService = new QuestionsAppService();
  }

  /**************************************************************************** */
  /**
   * GETメソッド
   * @param array $pathParams `api/v2/questions/{pathParams[0]}/{pathParams[1]}/...`
   * @return array レスポンス
   */
  public function get($pathParams) {
    try{
      switch($pathParams[0]){
        // 指定のインデックスから最新30件の質疑応答情報を取得
        case "list":
          $startIndex = $_GET['startIndex'] == 0 ? 99999 : $_GET['startIndex'];
          return $this->questionsAppService->getQuestionsFrom($startIndex);
        
        // 指定のインデックスの質疑応答情報を1件取得
        case is_numeric($pathParams[0]):
          $selectIndex = (int)$pathParams[0];
          return $this->questionsAppService->getSelectedQuestion($selectIndex);
        
        // 最新質問5件を取得(チャットボットの「みんなの質問を見せて」返答用)
        case "latest":
          return $this->questionsAppService->getLatestQuestions();

        // 無効なアクセス
        default:
          $this -> code = 400;
          return ["error" => [
            "type" => "invalid_access"
          ]];
      }
    }catch(NotFoundException $error){
      $this->code = $error->getCode();
      return ["error" => [
        "type" => "not_found_exception",
        "message" => json_decode($error->getMessage(), true),
        "info" => $error->getSource()
      ]];

    }catch(PDOException $error){
      $this->code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => json_decode($error, true)
      ]];

    }catch(Exception $error){
      $this->code = 500;
      return ["error" => [
        "type" => "unknown_exception",
        "message" => json_decode($error, true)
      ]];
    }
  }

  /**************************************************************************** */
  /**
   * POSTメソッド
   * @param array $pathParams `api/v2/questions/{pathParams[0]}/{pathParams[1]}/...`
   * @return array レスポンス
   */
  public function post($pathParams) {
    $post = $this->request_body;
    // TODO: userIdTokenのチェック方法を検討
    switch($pathParams[0]){
      case "newQuestion": // チャットボットから新規質問登録するときはuserIdTokenが取得できない
        break;
      //case "view_log":
      //case "isYourQuestion":
      default:
        if(!array_key_exists("userIdToken",$post)){
          $this->code = 400;
          return ["error" => [
            "type" => "user token is required"
          ]];
        }
        // ユーザーの存在確認
        include("users.php");
        $usersController = new UsersController();
        try{
          $userId = $usersController->verifyLine($post["userIdToken"])["sub"];
        }catch(Exception $error){
          $this->code = $error->getCode();
          return ["error" => json_decode($error->getMessage(),true)];
        }
        break;
    }
    
    switch($pathParams[0]){
      // 閲覧ログを記録する
      case "view_log":
        if(!is_numeric($pathParams[1])){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        try{
          $recordStatus = $this->insertViewingLog($userId, (int) $pathParams[1])["status"];
          $res["isYourQuestion"] = $this->checkIsYourQuestion((int) $pathParams[1], $userId)["isQuestioner"];
          $res["isAssigner"] = $res["isYourQuestion"] ? false : $this->checkIsAssigner((int) $pathParams[1], $userId)["isAssigner"];
          $this->code = $recordStatus;
          return $res;
        }catch(Exception $error){
          $this->code = json_decode($error->getMessage())->status;
          return ["error" => json_decode($error->getMessage(),true)];
        }
        break;
      
      // 質問者とユーザが一致するか
      case "isYourQuestion":
        if(!is_numeric($pathParams[1])){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }else{
          return $this->checkIsYourQuestion((int) $pathParams[1], $userId);
        }
        break;

      case "newQuestion":
        if(!array_key_exists("userId",$post) || 
          !array_key_exists("lectureNumber",$post) ||
          !array_key_exists("questionText",$post)
        ){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        
        $resInsert = $this->insertQuestionData($post["userId"], $post["lectureNumber"], $post["questionText"]);
        if(!array_key_exists("questionIndex", $resInsert)){
          $this->code = 500;
          return ["error" => $resInsert];
        }
        include("users.php");
        $usersController = new UsersController();
        $resAssign = $usersController->assignStudentAnswerer($post["userId"], $resInsert["questionIndex"]);
        if(!array_key_exists("assignedStudents", $resAssign)){
          $this->code = 500;
          return ["error" => $resAssign];
        }

        return [
          "questionIndex" => $resInsert["questionIndex"], 
          "assignedStudents" => $resAssign["assignedStudents"]
        ];
        break;

      // 無効なアクセス
      default:
        $this -> code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
    }
  }

  /**
   * 質疑LIFFページの閲覧ログの追加
   * @param string $lineId ユーザのLINE固有id
   * @param int $questionIndex 閲覧した質疑応答情報のインデックス
   * @return array DB追加の成功/失敗
   */
  private function insertViewingLog($lineId, $questionIndex) {

    require_once(dirname(__FILE__)."../../../../repository/LogRepository.php");
    $logRepository = new LogRepository();
    
    try {
      $savedLogIndex = $logRepository->savePageViewHistory($lineId, $questionIndex);
    
      $this->code = 201;
      return [
        "result" => "success",
        "savedLogIndex" => $savedLogIndex,
      ];
    
    }catch(Exception $error){
      $this->code = 500;
      return ["error" => [
        "type" => get_class($error),
        "message" => json_decode($error->getMessage(), true)
      ]];
    }
  }

  /**
   * 質問がアクセスしたユーザが投稿したものかチェックする
   * @param int $index 質問のインデックス
   * @param string $lineId ユーザID
   * @return array
   */
  private function checkIsYourQuestion($index, $lineId){
    $db = new DB();
  
    try{
      // mysqlの実行文(テーブルに指定のLINE IDが存在するかのみチェック)
      $stmt = $db -> pdo() -> prepare(
        "SELECT COUNT(*) 
        FROM `Questions` 
        WHERE `index`=:questionIndex AND `questionerId` = :questionerId LIMIT 1"
      );
      $stmt->bindValue(':questionIndex', $index, PDO::PARAM_STR);
      $stmt->bindValue(':questionerId', $lineId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return ["isQuestioner" => $stmt->fetchColumn() > 0];
      
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
    return [];
  }

  /**
   * ユーザが質問の回答者に割り振られているか確認する
   * @param int $index 質問のインデックス
   * @param string $lineId ユーザID
   * @return array
   */
  private function checkIsAssigner($index, $lineId){
    $db = new DB();
  
    try{
      // mysqlの実行文(テーブルに指定のLINE IDが存在するかのみチェック)
      $stmt = $db -> pdo() -> prepare(
        "SELECT COUNT(*) 
        FROM `Assignments` 
        WHERE `questionIndex`=:questionIndex AND `userUid` = :userUid LIMIT 1"
      );
      $stmt->bindValue(':questionIndex', $index, PDO::PARAM_INT);
      $stmt->bindValue(':userUid', $lineId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return ["isAssigner" => $stmt->fetchColumn() > 0];
      
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
    return [];
  }

  /**
   * 新規の質問をDBに登録する
   * @param string $userId 質問者のLINEid
   * @param int $lectureNumber 質問の対象となる講義の番号
   * @param string $question_text 質問文
   * @return array 結果
   */
  public function insertQuestionData($userId, $lectureNumber, $questionText) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      $stmtQA = $pdo -> prepare(
        "INSERT INTO Questions (questionerId, lectureNumber, questionText)
        VALUES (:questionerId, :lectureNumber, :questionText)"
      );
      //データの紐付け
      $stmtQA->bindValue(':questionerId', $userId, PDO::PARAM_STR);
      $stmtQA->bindValue(':lectureNumber', $lectureNumber, PDO::PARAM_INT);
      $stmtQA->bindValue(':questionText', $questionText, PDO::PARAM_STR);
      
      // 実行
      $resQA = $stmtQA->execute();
      $lastIndexQA = $pdo->lastInsertId();
      if(!$resQA){
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response",
          "message" => "fail to insert to Q&A Database"
        ]];
      }

      /* // mysqlの実行文の記述
      $stmtThread = $pdo -> prepare(
        "INSERT INTO Discussions (questionIndex, userId, userType, isQuestionersMessage, messageType, message)
        VALUES (
          :questionIndex, 
          :userId,
          'student',
          1,
          'chat', 
          :message
        )"
      );
      //データの紐付け
      $stmtThread->bindValue(':questionIndex', $lastIndexQA, PDO::PARAM_INT);
      $stmtThread->bindValue(':userId', $userId, PDO::PARAM_STR);
      $stmtThread->bindValue(':message', $questionText, PDO::PARAM_STR);
      
      // 実行
      $resThread = $stmtThread->execute();
      $lastIndexThread = $pdo->lastInsertId();

      if(!$resThread){
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response",
          "message" => "fail to insert to Thread Database"
        ]];
      } */

      $this->code = 201;
      //header("Location: ".$this->url.$lastIndexQA);

      // include(dirname( __FILE__)."/../utils/sendEmail.php");
      // sendEmailToInstructors("newQuestion", $questionText, $lastIndexQA);

      return [
        "questionIndex" => $lastIndexQA,
        //"discussionIndex" => $lastIndexThread
      ];

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
   * PUTメソッド
   * @param array $args [questionIndex, case]
   * @return array レスポンス
   */
  public function put($args) {
    if(!$this->is_set($args[0])){ //質問のインデックスが指定されていない
      $this->code = 400;
      return ["error" => [
        "type" => "invalid_url"
      ]];
    }
    $payload = $this->request_body;

    if(!array_key_exists("userIdToken",$payload)){
      $this->code = 400;
      return ["error" => [
        "type" => "user token is required"
      ]];
    }
    // ユーザーの存在確認
    include("users.php");
    $usersController = new UsersController();
    try{
      $userId = $usersController->verifyLine($payload["userIdToken"])["sub"];
    }catch(Exception $error){
      $this->code = $error->getCode();
      return ["error" => json_decode($error->getMessage(),true)];
    }

    switch($args[1]){
      case "answer":
        if(!array_key_exists("questionText",$payload)||
          !array_key_exists("answerText",$payload)||
          !array_key_exists("broadcast",$payload)||
          !array_key_exists("intentName",$payload)
        ){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }else{
          return $this->updateAnswer((int)$args[0], (int)$payload["broadcast"], $payload["questionText"], $userId, $payload["answerText"], $payload["intentName"]);
        }
        break;

      // 無効なアクセス
      default:
        $this -> code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
    }
  }

  /**
   * 質問に対する回答情報を更新する
   * @param int $questionIndex 更新する質疑応答情報のインデックス
   * @param int $broadcast 1:全体通知/0:個別通知
   * @param string $questionText 修正後の質問文
   * @param string $userUid 回答者のユーザーID
   * @param string $answerText 質問に対する応答文
   * @param string $intentName Dialogflowに登録されているインテント名(Format: projects/<Project ID>/agent/intents/<Intent ID>)
   * @return array DB更新結果 || エラーメッセージ
   */
  public function updateAnswer($questionIndex, $broadcast, $questionText, $userUid, $answerText, $intentName) {
    $db = new DB();
    try{
      // mysqlの実行文
      $stmt = $db->pdo() -> prepare(
        "UPDATE `Questions`
        SET `broadcast` = :broadcast,
            `questionText` = :questionText,
            `answerText` = :answerText,
            `respondentId` = :userUid,
            `intentName` = :intentName
        WHERE `Questions`.`index` = :questionIndex"
      );
      //データの紐付け
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':broadcast', $broadcast, PDO::PARAM_INT);
      $stmt->bindValue(':questionText', $questionText, PDO::PARAM_STR);
      $stmt->bindValue(':answerText', $answerText, PDO::PARAM_STR);
      $stmt->bindValue(':userUid', $userUid, PDO::PARAM_STR);
      $stmt->bindValue(':intentName', $intentName, PDO::PARAM_STR);
      
      // 実行
      $res = $stmt->execute();
      if($res){
        $this->code = 201;
        return [
          "index" => $questionIndex,
          "broadcast" => $broadcast,
          "questionText" => $questionText,
          "answerText" => $answerText,
          "intentName" => $intentName
        ];
      }else{
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response",
          "update_param" => [
            "index" => $questionIndex,
            "broadcast" => $broadcast,
            "question" => $questionText,
            "answer" => $answerText,
            "intentName" => $intentName
          ],
          "pdo" => $res
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
  public function options(){
    header("Access-Control-Allow-Methods: OPTIONS,GET,HEAD,POST,PUT,DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    return [];
  }

  private function is_set($value){
    return !(is_null($value) || $value === "");
  }
}