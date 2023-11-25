<?php

require_once(dirname(__FILE__) . "/../../../vendor/autoload.php");

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../../../"); //.envを読み込む
$dotenv->load();

/**
 * 教員にメール通知する
 * @param string $type 新規質問/メッセージ
 * @param string $messageText 本文
 * @param int $questionIndex 質問番号
 * @return bool 送信成功||失敗
 */
function sendEmailToInstructors($type, $messageText, $questionIndex)
{
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  //教員のメールアドレス
  $to = getenv("INSTRUCTOR_EMAIL");
  // error_log(print_r($to, true) . "\n", 3, dirname(__FILE__).'/debug.log');

  if ($type === "newQuestion") {
    $subject = "[SUSANbot] 新しい質問 が投稿されました";
  } else if ($type === "message") {
    $subject = "[SUSANbot] メッセージ が投稿されました";
  }
  $message = $messageText . "\r\n \r\n" .
    "確認する↓\r\n" .
    "https://susan2023-five.vercel.app/question/" . $questionIndex . "\r\n ";

  $headers = "From: noreply@susan.next.jp";

  return mb_send_mail("yoshino@wakayama-u.ac.jp", $subject, $message, $headers);
}

/**
 * 教員にメール通知する
 * @param string $type 新規質問/メッセージ
 * @param string $messageText 本文
 * @param int $questionIndex 質問番号
 * @param string $userId ユーザID
 * @return bool 送信成功||失敗
 */
function sendEmailToInstructorsWhithLogs($type, $messageText, $questionIndex, $userId)
{
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  //教員のメールアドレス
  $to = getenv("INSTRUCTOR_EMAIL");
  // error_log(print_r($to, true) . "\n", 3, dirname(__FILE__).'/debug.log');

  if ($type === "newQuestion") {
    $subject = "[SUSANbot] 新しい質問 が投稿されました";
  } else if ($type === "message") {
    $subject = "[SUSANbot] メッセージ が投稿されました";
  }
  $message = $messageText . "\r\n \r\n" .
    "確認する↓\r\n" .
    "https://susan2023-five.vercel.app/question/" . $questionIndex . "\r\n ";
    "\r\n" .
    "\r\n" .
    "ログの確認はこちら↓\r\n" .
    "https://www2.yoslab.net/~minari/susan/api/v2/logs?userId=" . $userId . "\r\n ";

  $headers = "From: noreply@susan.next.jp";

  return mb_send_mail("yoshino@wakayama-u.ac.jp", $subject, $message, $headers);
}

// echo sendEmailToInstructors("newquestion", "user_question_log", "5");