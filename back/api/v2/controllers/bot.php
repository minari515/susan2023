<?php
ini_set('display_errors',1);

require_once(dirname(__FILE__)."/../../../vendor/autoload.php");

use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;
use GuzzleHttp\Psr7\Message;
use function GuzzleHttp\Promise\exception_for;

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
  public $requestBody;

  function __construct()
  {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->httpClient = new CurlHTTPClient(getenv("LINE_ACCESS_TOKEN"));
    $this->bot = new LINEBot($this->httpClient, ['channelSecret' => getenv("LINE_CHANNEL_SECRET")]);
    $this->requestBody = file_get_contents('php://input');
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
  public function post($path) {
    switch($path[0]){
      // LINEbot の応答処理
      case "webhook":
        // 署名の存在確認
        if(empty($_SERVER['HTTP_X_LINE_SIGNATURE'])){
          $this->code = 400;
          return ["error" => [
            "type" => "signature_not_found"
          ]];
        }
        // 署名検証のため，request body をそのまま渡す
        return $this->webhook($this->requestBody, $_SERVER['HTTP_X_LINE_SIGNATURE']);
      
      // LINEbot のプッシュ通知処理
      case "push":
        // request bodyをUTF-8にエンコード -> PHPの連想配列に変換
        $req = json_decode(mb_convert_encoding($this->requestBody ,"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
        return $this->push($req);
      
      default:
        $this->code = 404;
        return ["error" => ["type" => "path_not_found"]];
    }
  }

  private function webhook($requestBody, $signature){
    $response = null;

    try {
      // LINEBotが受信したイベントオブジェクトを受け取る
      $events = $this->bot->parseEventRequest($requestBody, $signature);

    } catch (InvalidSignatureException $e) {
      $this->code = 400;
      error_log(print_r($e, true) . "\n", 3, dirname(__FILE__).'/debug.log');
      return ["error" => ["type" => "Invalid_signature"]];
    } catch (InvalidEventRequestException $e) {
      $this->code = 400;
      error_log(print_r($e, true) . "\n", 3, dirname(__FILE__).'/debug.log');
      return ["error" => ["type" => "Invalid_event_request"]];
    } catch (Exception $e) {
      $this->code = 400;
      error_log(print_r($e, true) . "\n", 3, dirname(__FILE__).'/debug.log');
      return ["error" => ["type" => "unknown_error"]];
    }

    foreach($events as $event){
      $replyToken = $event->getReplyToken(); // ReplyTokenの取得
      $eventType = $event->getType();

      if ($eventType === 'message') {
        // メッセージイベント
        $replyMessages = $this->handleMessageEvent($event);
        $response = $this->bot->replyMessage($replyToken, $replyMessages);

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
        error_log('Failed! '. $response->getHTTPStatus() . ' '.$response->getRawBody(), 3, dirname(__FILE__).'/debug.log');
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
    error_log(print_r($event, true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
    error_log("hogehoge" . "\n", 3, dirname(__FILE__).'/debug_event.log');
    $replyMessages = new MultiMessageBuilder();
    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    // APIに送信するパラメーター
    $data = [
      'model' => 'gpt-3.5-turbo',
      'messages' => [
        ['role' => 'system', 'content' => 'こんにちは！'],
        ['role' => 'user', 'content' => $event->getText()],
      ],
      'max_tokens' => 100,
    ];

    // Guzzleを使ってAPIにリクエストを送信する
    try {
      $client = new Client();
      $gptresponse = $client->post($apiUrl, [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . getenv("OPENAI_API_KEY"),
        ],
      'json' => $data,
      ]);
    } catch(Exception $e) {
      error_log(print_r($e, true) . "\n", 3, dirname(__FILE__).'/debug_error.log');
    }
    // APIからのレスポンスを取得する
    $result = json_decode($gptresponse->getBody()->getContents(), true);
    error_log(print_r($result, true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
    // 生成されたテキストを取得する
    $generatedText = $result['choices'][0]['message']['content'];
    error_log(print_r(json_decode($gptresponse->getBody()->getContents(), true), true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
    error_log(print_r($generatedText, true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
    $replyMessages->add(new TextMessageBuilder($generatedText));
    $replyMessages->add(new StickerMessageBuilder(1, 2));
    return $replyMessages;
  }

  public function push($requestBody){
    $response = $this->bot->broadcast(new TextMessageBuilder("ブロードキャスト通知テスト"));
    $this->code = $response->getHTTPStatus();
    return $response->getRawBody();
  }
}