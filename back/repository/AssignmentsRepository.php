<?php

// TODO: database.phpの配置を変更
require_once(dirname(__FILE__)."/../api/database.php");

class AssignmentsRepository {

  private $db;

  public function __construct() {
    $this->db = new DB();
  }

  /**
   * ユーザが質問の回答者に割り振られているか確認する
   * @param int $index 質問のインデックス
   * @param string $lineId ユーザID
   * @return bool 割り振られているかどうか
   */
  public function isAssigned($index, $lineId) {
    try{
      // mysqlの実行文(テーブルに指定のLINE IDが存在するかのみチェック)
      $stmt = $this->db -> pdo() -> prepare(
        "SELECT COUNT(*) 
        FROM `Assignments` 
        WHERE `questionIndex`=:questionIndex AND `userUid` = :userUid LIMIT 1"
      );
      $stmt->bindValue(':questionIndex', $index, PDO::PARAM_INT);
      $stmt->bindValue(':userUid', $lineId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return $stmt->fetchColumn() > 0;
      
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }
  
    } catch(PDOException $error){
      throw $error;
    }
  }
}
