<?php
ini_set('display_errors',1);

class QuestionnairesController
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
    if(!array_key_exists("userIdToken",$_GET)){
      $this->code = 400;
      return ["error" => [
        "type" => "user token is required"
      ]];
    }
    // ユーザーの存在確認
    include("users.php");
    $usersController = new UsersController();
    try{
      $userId = $usersController->verifyLine($_GET['userIdToken'])["sub"];
    }catch(Exception $error){
      $this->code = $error->getCode();
      return ["error" => json_decode($error->getMessage(),true)];
    }
    return $this->checkIsQuestionnaireCompleted($userId);
  }

  private function checkIsQuestionnaireCompleted($lineId){
    $db = new DB();

    try{
      // mysqlの実行文
      $stmt = $db -> pdo() -> prepare(
        "SELECT COUNT(*) 
        FROM `Questionnaire`
        WHERE `userUid` = :lineId"
      );
      // データの紐付け
      $stmt->bindValue(':lineId', $lineId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return ["isQuestionnaireCompleted" => $stmt->fetchColumn() > 0];
      
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

    if(!array_key_exists("questionnaire",$post)){
      $this->code = 400;
      return ["error" => [
        "type" => "invalid_param"
      ]];
    }else{
      return $this->insertEvaluationQuestionnaire($userId, $post["questionnaire"]);
    }
  }

  /**
   * 実験評価アンケートの回答をDBに追加
   * @param string $lineId ユーザのLINE固有id
   * @param array $questionnaire アンケートへの回答
   * @return array DB追加の成功/失敗
   */
  private function insertEvaluationQuestionnaire($lineId, $questionnaire) {
    $db = new DB();
    $pdo = $db -> pdo();

    $ColumnNames = "";
    $ValuesName = "";
    foreach($questionnaire as $key => $answer){
      foreach($answer as $type => $value){
        $ColumnNames .= $key.$type.",";
        $ValuesName .= ":".$key.$type.",";
      }
    }
    $ColumnNames = mb_substr($ColumnNames,0,-1);
    $ValuesName = mb_substr($ValuesName,0,-1);

    try{
      // mysqlの実行文の記述
      $stmt = $pdo -> prepare(
        "INSERT INTO Questionnaire (userUid, ".$ColumnNames.") VALUES (:userUid, ".$ValuesName.")"
      );
      //データの紐付け
      $stmt->bindValue(':userUid', $lineId, PDO::PARAM_STR);
      foreach($questionnaire as $key => $answer){
        foreach($answer as $type => $value){
          // データの型として，リッカートならint，それ以外ならstr
          $valueType = $type == "licart" ? PDO::PARAM_INT : PDO::PARAM_STR;
          $stmt->bindValue(":".$key.$type, $value, $valueType);
        }
      }
      // 実行
      $res = $stmt->execute();
      $lastIndex = $pdo->lastInsertId();
      if($res){
        $this->code = 201;
        //header("Location: ".$this->url.$lastIndex);
        return ["response" => "success!!"];
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

  /**************************************************************************** */
  /**
   * PUTメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function put($args) {
    return [];
  }

  /**************************************************************************** */
  /**
   * DELETEメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function delete($args) {
    return [];
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