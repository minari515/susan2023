<?php

// TODO: database.phpの配置を変更
require_once(dirname(__FILE__)."/../api/database.php");

class UsersRepository {

  private $db;

  public function __construct() {
    $this->db = new DB();
  }

  /**
   * 回答可能なユーザをランダムに選出する
   */
  public function findAnswerableUsersForRandomExclude($questionerId) {
    try{
      // mysqlの実行文の記述
      $stmt = $this->db -> pdo() -> prepare(
        "SELECT `userUid`
        FROM `Users`
        WHERE `userUid` != :questionerId AND `type` = 'student' AND `canAnswer` = 1
        ORDER BY RAND() LIMIT 10"
      );
      
      //データの紐付け
      $stmt->bindValue(':questionerId', $questionerId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();

      if($res){
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
      
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }
    
    } catch(PDOException $error){
      throw $error;
    }
  }
}
