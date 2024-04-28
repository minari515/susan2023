<?php

// TODO: database.phpの配置を変更
require_once(dirname(__FILE__)."/../api/database.php");

class LogRepository {

  private $db;

  public function __construct() {
    $this->db = new DB();
  }

  /**
   * ページビュー履歴を保存する
   * @param string $lineId ユーザーのLINEID
   * @param int $questionIndex 閲覧した質問のインデックス
   * @return int 保存したページビュー履歴のID
   * @throws Exception
   */
  public function savePageViewHistory($lineId, $questionIndex){
    try{
      // mysqlの実行文の記述
      // 指定されたインデックスの質問が存在しない場合はMySQL#1048エラー
      $stmt = $this->db -> pdo() -> prepare(
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
      
      if($res){
        return $this->db -> pdo()->lastInsertId();
      
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }

    } catch(PDOException $error){
      throw $error;
    }
  }
}
