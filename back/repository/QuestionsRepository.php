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

  /**
   * 指定のインデックスの質疑応答情報を取得する
   * @param int $index 質疑応答情報のインデックス
   * @return Object 質問データ
   */
  function findQuestion($index) {
    try{
      // mysqlの実行文
      $stmt = $this->db -> pdo() -> prepare(
        "SELECT `index`,`timestamp`,`lectureNumber`,`questionText`,`answerText`,`broadcast`,`intentName`
        FROM `Questions`
        WHERE `index` = :QuestionIndex"
      );
      //データの紐付け
      $stmt->bindValue(':QuestionIndex', $index, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }
    
    } catch(PDOException $error){
      throw $error;
    }
  }

  /**
   * 質問を新規登録する
   * @param string $userId ユーザID
   * @param int $lectureNumber 講義番号
   * @param string $questionText 質問内容
   * @return int 質問のインデックス
   */
  public function saveNewQuestion($userId, $lectureNumber, $questionText) {
    try{
      // mysqlの実行文の記述
      $stmt = $this->db -> pdo() -> prepare(
        "INSERT INTO Questions (questionerId, lectureNumber, questionText)
        VALUES (:questionerId, :lectureNumber, :questionText)"
      );
      //データの紐付け
      $stmt->bindValue(':lectureNumber', $lectureNumber, PDO::PARAM_INT);
      $stmt->bindValue(':questionText', $questionText, PDO::PARAM_STR);
      $stmt->bindValue(':questionerId', $userId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
  
      if($res){
        return $this->db->pdo()->lastInsertId();
      
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }
    
    } catch(PDOException $error){
      throw $error;
    }
  }

  /**
   * 質問情報を更新する
   * @param int $questionIndex 質問のインデックス
   * @param int $broadcast 1:全体通知/0:個別通知
   * @param string $questionText 修正後の質問文
   * @param string $answerText 質問に対する応答文
   * @param string $intentName Dialogflowインテント名
   * @param string $respondentId 回答者のユーザID
   * @return array 更新された質問情報
   */
  public function updateQuestion($questionIndex, $broadcast, $questionText, $answerText, $intentName, $respondentId) {
    try{
      // mysqlの実行文
      $stmt = $this->db ->pdo() -> prepare(
        "UPDATE `Questions`
        SET `broadcast` = :broadcast,
            `questionText` = :questionText,
            `answerText` = :answerText,
            `respondentId` = :respondentId,
            `intentName` = :intentName
        WHERE `Questions`.`index` = :questionIndex"
      );
      //データの紐付け
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      $stmt->bindValue(':broadcast', $broadcast, PDO::PARAM_INT);
      $stmt->bindValue(':questionText', $questionText, PDO::PARAM_STR);
      $stmt->bindValue(':answerText', $answerText, PDO::PARAM_STR);
      $stmt->bindValue(':respondentId', $respondentId, PDO::PARAM_STR);
      $stmt->bindValue(':intentName', $intentName, PDO::PARAM_STR);
      
      // 実行
      $res = $stmt->execute();
      if($res){
        return true;
      }else{
        throw new Exception("PDOの実行に失敗しました");
      }

    } catch(PDOException $error){
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }

  /**
   * 指定インデックスの質問が特定のユーザによるものかを確認する
   * @param int $index 質問のインデックス
   * @param string $userId ユーザID
   * @return bool
   */
  function isQuestionByUser($index, $userId) {
    try{
      // mysqlの実行文(テーブルに指定のLINE IDが存在するかのみチェック)
      $stmt = $this->db -> pdo() -> prepare(
        "SELECT COUNT(*) 
        FROM `Questions` 
        WHERE `index`=:questionIndex AND `questionerId` = :questionerId LIMIT 1"
      );
      $stmt->bindValue(':questionIndex', $index, PDO::PARAM_STR);
      $stmt->bindValue(':questionerId', $userId, PDO::PARAM_STR);
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
