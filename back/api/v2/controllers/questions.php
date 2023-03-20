<?php
ini_set('display_errors',1);

class QuestionsController
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
    switch($args[0]){
      // 指定のインデックスから最新30件の質疑応答情報を取得
      case "list":
        return $this->getQuestionsData($_GET['startIndex']);
        break;
      
      // 指定のインデックスの質疑応答情報を1件取得
      case is_numeric($args[0]):
        return $this->getSelectedQuestionData($args[0]);
        break;
      
      // 最新質問5件を取得(チャットボットの「みんなの質問を見せて」返答用)
      case "latest":
        return $this->getLatestQuestionsData();
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
   * 指定のインデックスを起点に最新30件の質疑応答情報を取得する
   * TODO: LINEbotから呼び出せるようにpublicだが，privateにしておきたい
   * @param int $startIndex 質疑応答情報のインデックス
   * @return array 質問データ
   */
  public function getQuestionsData($startIndex) {
    if($startIndex == 0) $startIndex = 99999;
    $db = new DB();

    try{
      // mysqlの実行文
      $stmt = $db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`lectureNumber`,`questionText`,`answerText`,`broadcast`,`intentName`
        FROM `Questions`
        WHERE `index` < :startIndex
        ORDER BY `Questions`.`index` DESC
        LIMIT 30"
      );
      //データの紐付け
      $stmt->bindValue(':startIndex', $startIndex, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        //$questions = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($questions as $key => $question){
          $questions[$key]["broadcast"] = (bool)$question["broadcast"];
        }
        return $questions;
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

  /**
   * 指定のインデックスの質疑応答情報を取得する
   * @param int $index 質疑応答情報のインデックス
   */
  private function getSelectedQuestionData($index) {
    $db = new DB();

    try{
      // mysqlの実行文
      $stmt = $db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`lectureNumber`,`questionText`,`answerText`,`broadcast`,`intentName`
        FROM `Questions`
        WHERE `index` = :QuestionIndex"
      );
      //データの紐付け
      $stmt->bindValue(':QuestionIndex', $index, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        $question = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($question)){
          $question[0]["broadcast"] = (bool)$question[0]["broadcast"];
          return $question[0];
        }else{ //指定したインデックスの質問が存在しない場合
          $this->code = 404;
          return ["error" => [
            "type" => "not_in_sample"
          ]];
        }
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

  /**
   * 最新質問5件を取得(チャットボットの「みんなの質問を見せて」返答用)
   * @return array 質問データ
   */
  private function getLatestQuestionsData(){
    $db = new DB();
    try{
      // mysqlの実行文
      $stmt = $db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`lectureNumber`,`questionText`,`answerText`,`broadcast`,`intentName`
        FROM `Questions`
        ORDER BY `Questions`.`index` DESC
        LIMIT 5"
      );
      // 実行
      $res = $stmt->execute();
  
      if($res){
        //$questions = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $questions;
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
   * @param array $args 追加情報のタイプ
   * @return array レスポンス
   */
  public function post($args) {
    $post = $this->request_body;
    // TODO: userIdTokenのチェック方法を検討
    switch($args[0]){
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
    
    switch($args[0]){
      // 閲覧ログを記録する
      case "view_log":
        if(!is_numeric($args[1])){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        try{
          $recordStatus = $this->insertViewingLog($userId, (int) $args[1])["status"];
          $res["isYourQuestion"] = $this->checkIsYourQuestion((int) $args[1], $userId)["isQuestioner"];
          $res["isAssigner"] = $res["isYourQuestion"] ? false : $this->checkIsAssigner((int) $args[1], $userId)["isAssigner"];
          $this->code = $recordStatus;
          return $res;
        }catch(Exception $error){
          $this->code = json_decode($error->getMessage())->status;
          return ["error" => json_decode($error->getMessage(),true)];
        }
        break;
      
      // 質問者とユーザが一致するか
      case "isYourQuestion":
        if(!is_numeric($args[1])){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }else{
          return $this->checkIsYourQuestion((int) $args[1], $userId);
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
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      // 指定されたインデックスの質問が存在しない場合はMySQL#1048エラー
      $stmt = $pdo -> prepare(
        "INSERT INTO PageViewHistories (userUid, questionIndex, isQuestionerViewing)
        VALUES (
          :userUid,
          (SELECT `index` FROM `Questions` WHERE `index` = :questionIndex), 
          (SELECT COUNT(*) FROM `Questions` WHERE `index`=:qIndex AND `questionerId` = :questionerId LIMIT 1)
        )"
      );
      //データの紐付け
      $stmt->bindValue(':userUid', $lineId, PDO::PARAM_STR);
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':qIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':questionerId', $lineId, PDO::PARAM_STR);
      
      // 実行
      $res = $stmt->execute();
      $lastIndex = $pdo->lastInsertId();
      if($res){
        $this->code = 201;
        //header("Location: ".$this->url.$lastIndex);
        return [
          "result" => "success",
          "status" => 201
        ];
      }else{
        throw new ErrorException(json_encode([
          "error" => [
            "type" => "pdo_not_response"
          ],
          "status" => 500
        ]));
      }

    } catch(PDOException $error){
      throw new PDOException(json_encode([
          "error" => [
            "type" => "pdo_exception",
            "message" => $error->getMessage()
          ],
          "status" => 500
        ]));
    } catch(Exception $error){
      if(json_decode($error->getMessage())->error->type == "pdo_not_response"){
        throw $error;
      }else{
        throw new ErrorException(json_encode([
            "error" => [
              "type" => "unknown_exception",
              "message" => $error->getMessage()
            ],
            "status" => 500
          ]));
      }
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
  private function insertQuestionData($userId, $lectureNumber, $questionText) {
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

      include(dirname( __FILE__)."/../utils/sendEmail.php");
      sendEmailToInstructors("newQuestion", $questionText, $lastIndexQA);

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
  private function updateAnswer($questionIndex, $broadcast, $questionText, $userUid, $answerText, $intentName) {
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