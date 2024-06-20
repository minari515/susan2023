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
    
    try{
      switch($pathParams[0]){
        // 閲覧ログを記録する
        case "view_log":
          if(!is_numeric($pathParams[1]) || !array_key_exists("userIdToken",$post)){
            $this->code = 400;
            return ["error" => [
              "type" => "invalid_param",
              "info" => "questionIndex: ".$pathParams[1].", userIdToken: ".is_null($post["userIdToken"]) 
            ]];
          }
          $viewedQuestionIndex = (int) $pathParams[1];
          $viewerIdToken = $post["userIdToken"];
          
          $res = $this->questionsAppService->recordQuestionView($viewedQuestionIndex, $viewerIdToken);
          $this->code = 201;
          return $res;
        
        // 質問者とユーザが一致するか
        case "isYourQuestion":
          if(!is_numeric($pathParams[1]) || !array_key_exists("userIdToken",$post)){
            $this->code = 400;
            return ["error" => [
              "type" => "invalid_param",
              "info" => "questionIndex: ".$pathParams[1].", userIdToken: ".is_null($post["userIdToken"]) 
            ]];
          }

          $questionIndex = (int) $pathParams[1];
          $userIdToken = $post["userIdToken"];
          return $this->questionsAppService->checkIsYourQuestion($questionIndex, $userIdToken);

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
          
          $response = $this->questionsAppService->postQuestion($post["userId"], $post["lectureNumber"], $post["questionText"]);
          $this->code = 201;
          return $response;

        // 無効なアクセス
        default:
          $this -> code = 400;
          return ["error" => [
            "type" => "invalid_access"
          ]];
      }

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
    $questionIndex = $args[0];
    $payload = $this->request_body;

    if(!array_key_exists("userIdToken",$payload)){
      $this->code = 400;
      return ["error" => [
        "type" => "user token is required"
      ]];
    }
    $userIdToken = $payload["userIdToken"];

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
        }
        $isProcessSucceeded = $this->questionsAppService->updateAnswerToQuestion($userIdToken, $questionIndex, $payload["broadcast"], $payload["questionText"], $payload["answerText"], $payload["intentName"]);
        $this->code = $isProcessSucceeded ? 201 : 200;
        return [
          "index" => $questionIndex,
          "broadcast" => $payload["broadcast"],
          "questionText" => $payload["questionText"],
          "answerText" => $payload["answerText"],
          "intentName" => $payload["intentName"]
        ];

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