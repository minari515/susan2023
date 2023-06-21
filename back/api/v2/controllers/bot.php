<?php
ini_set('display_errors',1);

require_once(dirname(__FILE__)."/../../../vendor/autoload.php");

use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;


use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__."/../../../"); //.envを読み込む
$dotenv->load();

/**
 * LINE botのWebhookコントローラー
 */
class BotController
{
  public $code = 200;
  public $url;
  public $httpClient;
  public $bot;
  public $signature;
  public $requestBody;

  function __construct()
  {
    error_log(print_r($this->code, true) . "\n", 3, dirname(__FILE__).'/debugA.log');
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->httpClient = new CurlHTTPClient(getenv("LINE_ACCESS_TOKEN"));
    $this->bot = new LINEBot($this->httpClient, ['channelSecret' => getenv("LINE_CHANNEL_SECRET")]);
    $this->requestBody = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  /**************************************************************************** */
  /**
   * GETメソッド (動作確認用)
   * @param array $args
   * @return array レスポンス
   */
  public function get($args){
    $this->code = 200;
    return "I'm alive!!";
  }

  /**************************************************************************** */
  /**
   * POSTメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function post($args) {
    error_log(print_r($args) . "\n", 3, dirname(__FILE__).'/debugA.log');
    $this->signature = $_SERVER['HTTP_' . HTTPHeader::LINE_SIGNATURE];
    if(empty($this->signature)){
      $this->code = 200;
      return ["error" => [
        "type" => "signature_not_found"
      ]];
    }

    $post = $this->requestBody;
    $response = null;

    try {
      // LINEBotが受信したイベントオブジェクトを受け取る
      $events = $this->bot->parseEventRequest($post, $this->signature);
      // error_log(print_r($events, true) . "\n", 3, dirname(__FILE__).'/debug.log');

    } catch (InvalidSignatureException $e) {
      $this->code = 400;
      return ["error" => ["type" => "Invalid_signature"]];
    } catch (UnknownEventTypeException $e) {
      $this->code = 400;
      return ["error" => ["type" => "Unknown_event_type_has_come"]];
    } catch (UnknownMessageTypeException $e) {
      $this->code = 400;
      return ["error" => ["type" => "Unknown_message_type_has_come"]];
    } catch (InvalidEventRequestException $e) {
      $this->code = 400;
      return ["error" => ["type" => "Invalid_event_request"]];
    } catch (Exception $e) {
      $this->code = 400;
      return ["error" => ["type" => "unknown_error"]];
    }

    foreach($events as $event){
      $replyToken = $event->getReplyToken(); // ReplyTokenの取得
      $eventType = $event->getType();

      if ($eventType === 'message') {
        // メッセージイベント
        var_dump($args);
        $replyMessages = $this->handleMessageEvent($event);
        error_log(print_r($replyMessages, true) . "\n", 3, dirname(__FILE__).'/debug.log');
        $response = $this->bot->replyMessage($replyToken, $replyMessages);
        error_log(print_r($response, false) . "\n", 3, dirname(__FILE__).'/debug.log');
        error_log(print_r($response, true) . "\n", 3, dirname(__FILE__).'/debug.log');

      } else if ($eventType === 'follow') {
        // フォローイベント(友達追加・ブロック解除時)
        $replyMessages = $this->handleFollowEvent($event);
        $response = $this->bot->replyMessage($replyToken, $replyMessages);

      } else if ($eventType === 'postback') {
        // ボタンなど押した場合
        continue;

      } else {
        continue;
      }

      // 送信失敗の場合はサーバーのエラーログへ
      if(!$response->isSucceeded()){
        error_log('Failed! '. $response->getHTTPStatus() . ' '.$response->getRawBody(), 3, dirname(__FILE__).'/debugC.log');
      }

      $this->code = $response->getHTTPStatus();
      return $response->getRawBody();
    }
  }

  public function handleFollowEvent($event){
    $replyMessages = new MultiMessageBuilder();
    $replyMessages->add(new TextMessageBuilder("友達追加ありがとう！"));
    $replyMessages->add(new StickerMessageBuilder(1, 1));
    return $replyMessages;
  }

  public function handleMessageEvent($event){
    $replyMessages = new MultiMessageBuilder();
    $replyMessages->add(new TextMessageBuilder("メッセージありがとう！"));
    $replyMessages->add(new StickerMessageBuilder(1, 2));
    return $replyMessages;
  }
}