<?php

// TODO: database.phpの配置を変更
require_once(dirname(__FILE__)."/../api/database.php");

class QuestionRepository {

  private $db;

  public function __construct() {
    $this->db = new DB();
  }

  /**
   * 指定のインデックスを起点に質疑応答情報を複数取得する
   * @param int $range 取得する質問の数
   * @param int $startIndex 取得の起点となる質問のインデックス
   */
  function findQuestions($range, $startIndex = 99999){

    try{
      // mysqlの実行文
      $stmt = $this->db -> pdo() -> prepare(
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }
    
    } catch(PDOException $error){
      throw $error;
    }
  }
}
