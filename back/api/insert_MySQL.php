<?php

require_once dirname(__FILE__) . "/connect_MySQL.php";

/**
 * 教員・TAのリストにユーザを追加
 * @param string $userId ユーザのLINE固有id
 * @param array $questionnaire アンケートへの回答
 * @return bool $result DB追加の成功/失敗
 */
function insertInstructorUserDataToMySQL($userId, $displayName) {
  $pdo = connectMysql();  //PDO生成

  try{
    // mysqlの実行文の記述
    $stmt = $pdo -> prepare(
      "INSERT INTO instructor_list (LineId, InstructorName)
       VALUES (:LineId, :InstructorName)"
    );
    //データの紐付け
    $stmt->bindValue(':LineId', $userId, PDO::PARAM_STR);
    $stmt->bindValue(':InstructorName', $displayName, PDO::PARAM_STR);
    // 実行
    $result = $stmt->execute();
    return $result;

  } catch(PDOException $error){
    error_log(print_r($error, true) . "\n", 3, dirname(__FILE__).'/debug.log');

  } finally{
    //echo "finally!!";
  } 
}

/**
 * LINEボットとの会話ログをDBに挿入する
 * @param string $userId LINEボットユーザのID
 * @param string $user_message ユーザもしくはBotが送信したメッセージ(テキスト以外の場合はunknown_messageを想定)
 * @param string $sender 送信者(学生student，システムBotを想定
 * @return bool $result DB処理実行結果(成功/失敗)
 */
function insertConverationToMySQLForInstractors($userId, $user_message, $sender) {
  $pdo = connectMysql();  //PDO生成

  try{
    // mysqlの実行文の記述
    $stmt = $pdo -> prepare(
      "INSERT INTO pro_conversation (LineId, MessageText, Sender)
       VALUES (:LineId, :MessageText, :Sender)"
    );
    //データの紐付け
    $stmt->bindValue(':LineId', $userId, PDO::PARAM_STR);
    $stmt->bindValue(':MessageText', $user_message, PDO::PARAM_STR);
    $stmt->bindValue(':Sender', $sender, PDO::PARAM_STR);
    
    // 実行
    $result = $stmt->execute();
    return $result;

  } catch(PDOException $error){
    //echo $error;
  } finally{
    //echo "finally!!";
  } 

}