<?php
ini_set('display_errors',1);

/**
 * LINEのユーザ情報・対話ログのコントローラー
 */
class LineController
{
  public $code = 200;
  public $url;
  public $request_body;

  function __construct()
  {
    $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
    $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
  }

  /**************************************************************************** */
  /**
   * GETメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function get($args){
    switch($args[0]){
      case "context":
        if(!$args[1]){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param",
            "message" => "userId is required"
          ]];
        }
        return $this->getLatestContext($args[1]);
        break;

      case "question":
        if(!$args[1]){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param",
            "message" => "userId is required"
          ]];
        }
        return $this->getUserInputQuestion($args[1]);
        break;
      
      default:
        $this->code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
    }
  }

  /**
   * ボットとの直前9時間以内の会話におけるコンテキストを取得する
   * @param string $lineid ユーザのLINEid
   */
  private function getLatestContext($userUid) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // 直前９時間以内のチャットボット側の最新応答メッセージを取得
      $stmt = $pdo -> prepare(
        "SELECT `contextName`, `lifespanCount`
        FROM `BotTalkLogs` 
        WHERE userUid = :userUid AND timestamp >= DATE_SUB(NOW(),INTERVAL 9 HOUR) AND sender = 'bot' AND contextName IS NOT NULL
        ORDER BY `BotTalkLogs`.`index`  DESC
        LIMIT 1"
      );
      $stmt->bindValue(':userUid', $userUid, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();

      if($res){ //成功
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(is_array($data) && empty($data)){
          return [["name" => null, "lifespanCount" => null]];
        }else{
          $contexts = [];
          foreach($data as $context){
            $contexts[] = [
              "name" => $context["contextName"],
              "lifespanCount" => $context["lifespanCount"]
            ];
          }
          return $contexts;
        }

      }else{ //失敗
        $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                      dirname( __FILE__)."/line.php"."\n".
                      "pdo_not_response"."\n\n";
        error_log($log_message, 3, dirname( __FILE__).'/debug.log');
        $this -> code = 500;
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                      dirname( __FILE__)."/line.php"."\n".
                      print_r($error, true)."\n\n";
      error_log($log_message, 3, dirname( __FILE__).'/debug.log');
      $this -> code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }

  /**
   * DBからユーザが入力した質問文を取得する
   * @param string $userId LINEのユーザID
   * @return object $result 質問文
   */
  private function getUserInputQuestion($userId) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      $stmt = $pdo -> prepare(
        "SELECT `message`
        FROM `BotTalkLogs` 
        WHERE userUid = :userUid AND sender = 'student' AND (contextName = 'questionstart-followup' OR contextName = 'checktoasktheteacherdirectly-yes-followup')
        ORDER BY `BotTalkLogs`.`index`  DESC
        limit 3"
      );
      $stmt->bindValue(':userUid', $userId, PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();

      if($res){ //成功
        $inputs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach($inputs as $input){
          if($input != "質問を送信") return $input;
        }
        return null;

      }else{ //失敗
        $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                      dirname( __FILE__)."/line.php"."\n".
                      "pdo_not_response"."\n\n";
        error_log($log_message, 3, dirname( __FILE__).'/debug.log');
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                      dirname( __FILE__)."/line.php"."\n".
                      print_r($error, true)."\n\n";
      error_log($log_message, 3, dirname( __FILE__).'/debug.log');
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }

  /**************************************************************************** */
  /**
   * POSTメソッド
   * @param array $args
   * @return array レスポンス
   */
  public function post($args) {
    $post = $this->request_body;
    switch($args[0]){
      case "message":
        if(!array_key_exists("userId",$post) ||
          !array_key_exists("messageType",$post) ||
          !array_key_exists("message",$post) ||
          !array_key_exists("sender",$post)
        ){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        return $this->insertConversation($post["userId"], $post["sender"], $post["messageType"], $post["message"], $post["contextName"], (int) $post["lifespanCount"]);
        break;
      default:
        $this->code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
    }
  }

  /**
   * LINEボットとの会話ログをDBに挿入する
   * @param string $userId LINEボットユーザのID
   * @param string $message_type メッセージの形式(text, image, stickerなど)
   * @param string $user_message ユーザもしくはBotが送信したメッセージ(テキスト以外の場合はunknown_messageを想定)
   * @param string $sender 送信者(学生student，システムBotを想定)
   * @param string $context_name Dialogflowで設定したコンテキスト名
   * @param int $lifespan_count Dialogflowで設定したコンテキスト持続回数
   * @return array 更新完了 ? 空配列 : エラーメッセージ
   */
  private function insertConversation($userId, $sender, $messageType, $userMessage, $contextName, $lifespanCount) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      $stmt = $pdo -> prepare(
        "INSERT INTO BotTalkLogs (userUid, sender, messageType, message, contextName, lifespanCount)
        VALUES (:userUid, :sender, :messageType, :message, :contextName, :lifespanCount)"
      );
      //データの紐付け
      $stmt->bindValue(':userUid', $userId, PDO::PARAM_STR);
      $stmt->bindValue(':sender', $sender, PDO::PARAM_STR);
      $stmt->bindValue(':messageType', $messageType, PDO::PARAM_STR);
      $stmt->bindValue(':message', $userMessage, PDO::PARAM_STR);
      $stmt->bindValue(':contextName', $contextName, PDO::PARAM_STR);
      $stmt->bindValue(':lifespanCount', $lifespanCount, PDO::PARAM_INT);
      
      // 実行
      $res = $stmt->execute();
      $lastIndex = $pdo->lastInsertId();

      if($res){
        $this->code = 201;
        return [];
        
      }else{
        $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                      dirname( __FILE__)."/line.php"."\n".
                      "pdo_not_response"."\n\n";
        error_log($log_message, 3, dirname( __FILE__).'/debug.log');
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $log_message = print_r(date("Y/m/d H:i:s"), true)."\n".
                    dirname( __FILE__)."/line.php > insertConversationToMySQL"."\n".
                    print_r($error, true)."\n\n";
      error_log($log_message, 3, dirname( __FILE__).'/debug.log');
      $this->code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }
}
