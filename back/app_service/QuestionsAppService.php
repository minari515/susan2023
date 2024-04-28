<?php

class QuestionsAppService {

  /**
    * 指定のインデックスを起点に最新30件の質疑応答情報を取得する
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
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
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
  public function getSelectedQuestionData($index) {
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
          return ["error" => [
            "type" => "not_in_sample"
          ]];
        }
      }else{
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }
    } catch(PDOException $error){
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
  public function getLatestQuestionsData(){
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
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }
}
