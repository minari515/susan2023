<?php

require_once(dirname(__FILE__).'../../models/Question.php');

class QuestionService {

  public function __construct() {
  }

  /**
   * 指定のインデックスを起点に質疑応答情報を取得する
   * @param int $range 取得する質問の数
   * @param int $startIndex 取得の起点となる質問のインデックス
   * @return QuestionEntity[] 質問データの配列
   */
  public function getQuestions($range = 30, $startIndex = 99999) {
    $questions = [];

    $db = new DB();

    try{
      // mysqlの実行文
      $stmt = $db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`lectureNumber`,`questionText`,`answerText`,`broadcast`,`intentName`
        FROM `Questions`
        WHERE `index` < :startIndex
        ORDER BY `Questions`.`index` DESC
        LIMIT :range"
      );
      //データの紐付け
      $stmt->bindValue(':startIndex', $startIndex, PDO::PARAM_INT);
      $stmt->bindValue(':range', $range, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();

      if($res){
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $key => $question){
          $questions[] = new QuestionEntity(
            $question["index"],
            $question["timestamp"],
            $question["lectureNumber"],
            $question["questionText"],
            $question["answerText"],
            $question["broadcast"],
            $question["intentName"]
          );
        }
        return $questions;

      }else{
        throw new Exception("PDOの実行に失敗しました");
      }

    } catch(PDOException $error){
      throw $error;
    }
  }
}
