<?php
// LINEBotSDKの読み込み
require(dirname( __FILE__).'/../../susan_pro/vendor/autoload.php');

use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

/**
 * 学生からの投稿を教員側の専用ボットに通知する
 * @param string $type 質問/要望
 * @param array $payload 送信するデータ
 * @return string $result 送信結果(json?)
 */
function callbackToSusanPro($type, $payload){

  //チャンネルシークレット
  $channel_secret = file_get_contents(dirname( __FILE__).'/../../Config/susanpro_LINE_channel_secret.txt');
  //アクセストークン
  $channel_access_token = file_get_contents(dirname( __FILE__).'/../../Config/susanpro_LINE_access_token.txt');

  // LINEBotSDKの設定
  $http_client = new CurlHTTPClient($channel_access_token);
  $bot = new LINEBot($http_client, ['channelSecret' => $channel_secret]);

  //最終的に送信する複数のメッセージオブジェクト
  $push_messages = new MultiMessageBuilder();

  if($type === "question"){
    $flexMessage = questionFlex($payload);
  }else if($type === "request"){
    $flexMessage = requestFlex($payload['message']);
  }
  
  $push_messages->add($flexMessage);
  
  // プッシュメッセージを送信
  $response = $bot->broadcast($push_messages); // 登録者全員に通知
  //$response = $bot->pushMessage('Ueab1927685311694576468ee825137d3', $push_messages); // テスト用(開発者に送信)

  // 受信したメッセージをデータベースに保存
  insertConverationToMySQLForInstractors("broadcast", "【{$type}】{$payload['message']}", "bot");

  return $response->getHTTPStatus() . ' ' . $response->getRawBody();

}

function questionFlex($payload){
  ####### Body要素 ##############
  // 太字のテキスト要素を作る
  $titleText = new TextComponentBuilder("新しい質問です！");
  $titleText -> setWeight('bold');
  $titleText -> setSize('lg');

  // 通常のテキスト要素を作る
  $planeText = new TextComponentBuilder($payload['message']);
  $planeText -> setWrap(true);
  $planeText -> setMargin('md');

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $bodyBox = new BoxComponentBuilder('vertical',[$titleText, $planeText]);

  ####### Footer要素 ################
  // ボタンを押した際のアクションを設定
  $linkAction = new UriTemplateActionBuilder("確認する", "https://liff.line.me/1656210261-2116WaLZ/qanda_setter?index=".$payload['index']);

  // ボタンを作ってアクションを載せる
  $linkButton = new ButtonComponentBuilder($linkAction);

  // ボタンを水平に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('horizontal',[$linkButton]);

  // FlexMessage１つ分(バブル)を作って先程のボックスをそれぞれBodyとFooterに格納
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setBody($bodyBox);
  $flexContainer -> setFooter($footerBox);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('新しい質問です！',$flexContainer);

  return $flexMessage;
}

function requestFlex($request){
  ####### Body要素 ##############
  // 太字のテキスト要素を作る
  $titleText = new TextComponentBuilder("要望が来てます！");
  $titleText -> setWeight('bold');
  $titleText -> setSize('lg');

  // 通常のテキスト要素を作る
  $planeText = new TextComponentBuilder($request);
  $planeText -> setWrap(true);
  $planeText -> setMargin('md');

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $bodyBox = new BoxComponentBuilder('vertical',[$titleText, $planeText]);

  // FlexMessage１つ分(バブル)を作って先程のボックスをそれぞれBodyとFooterに格納
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setBody($bodyBox);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('要望が来てます！',$flexContainer);

  return $flexMessage;
}

/**
 * 教員にメール通知する
 * @param string $type 新規質問/メッセージ
 * @param array $payload 送信するデータ
 * @return bool 送信成功||失敗
 */
function sendEmailToInstructors($type, $payload){
  mb_language("Japanese");
  mb_internal_encoding("UTF-8");

  // $to = file_get_contents(dirname( __FILE__).'/../../Config/InstructorsAddress.txt');
  $to = getenv("INSTRUCTOR_EMAIL");
  error_log(print_r($to, true) . "\n", 3, dirname(__FILE__).'/debug.log');
  
  if($type === "newquestion"){
    $subject = "[SUSANbot] 新しい質問 が投稿されました";
  }else if($type === "message"){
    $subject = "[SUSANbot] メッセージ が投稿されました";
  }
  $message = $payload['message']."\r\n \r\n".
              "確認する↓\r\n".
              "https://liff.line.me/1660896972-Xol6KpBrqanda_setter?index=".$payload['index']."\r\n ";

  // $headers = "From: ".file_get_contents(dirname( __FILE__).'/../../Config/MyAddress.txt');
  $headers = "From: " .getenv("INSTRUCTOR_EMAIL");

  return mb_send_mail($to, $subject, $message, $headers); 
}