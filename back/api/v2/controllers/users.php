<?php
ini_set('display_errors',1);

require_once(dirname(__FILE__)."/../../../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__."/../../../"); //.envを読み込む
$dotenv->load();

class UsersController
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
  public function get($args) {
    if(!array_key_exists("userIdToken",$_GET)){
      $this->code = 400;
      return ["error" => [
        "type" => "invalid_param"
      ]];
    }
    try{
      $userId = $this->verifyLine($_GET["userIdToken"])["sub"];
      $result = $this->getUserInfo($userId);
      $this -> code = $result["status"];
      return $result["response"];
    }catch(Exception $error){
      $result = json_decode($error->getMessage(), true);
      $this -> code = $result["status"];
      return $result["error"];
    }
  }

  /**
   * DBからユーザ情報を取得する
   * @param string $userUid LINEのユーザID
   * @return array 
   */
  public function getUserInfo($userUid) {
    $db = new DB();
    $pdo = $db -> pdo();
    try{
      // 教員・TAか確認する
      $stmt = $pdo -> prepare(
        "SELECT *
        FROM `Users` 
        WHERE userUid = :userUid"
      );
      $stmt->bindValue(':userUid', $userUid, PDO::PARAM_STR);
      $res = $stmt->execute();
  
      if($res){
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($user) != 0){
          return [ "status" => 200, "response" => $user[0] ];
        }else{ //存在しない場合
          throw new Exception(json_encode([
            "status" => 404, 
            "error" => [
              "message" => "User not found."
            ]
          ]));
        }
      }else{
        throw new Exception(json_encode([
          "status" => 500, 
          "error" => [
            "message" => "pdo not response"
          ]
        ]));
      }
    } catch(PDOException $error){
      throw new Exception(json_encode([ 
        "status" => 500, 
        "error" => [
          "type" => "pdo_exception",
          "message" => json_decode($error->getMessage()),
          "verifiedId" => $userUid
        ],
      ]));
    }
  }

  /**
   * 質問者のLINE IDを取得する
   * @param int $questionId 質問ID
   * @return array lineId: string 質問者のLINE ID
   */
  public function getQuestionerLineId($questionIndex){
    $db = new DB();
    $pdo = $db -> pdo();
    
    try{
      // mysqlの実行文
      $stmt = $pdo -> prepare(
        "SELECT questionerId
        FROM `Questions` 
        WHERE `Questions`.`index` = :questionId"
      );
      //データの紐付け
      $stmt->bindValue(':questionId', $questionIndex, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();

      if(!$res){
        throw new Exception('pdo not response');
      }else{
        $questionerId = $stmt->fetchAll(PDO::FETCH_COLUMN)[0];
        return [ "lineId" => $questionerId ];
      }
    } catch(PDOException $error){
      $this -> code = 500;
      return [
        "error" => [
          "type" => "pdo_exception",
          "message" => $error->getMessage()
        ],
      ];
    } finally{
      //echo "finally!!";
    } 
  }

  /**
   * 質問に割り振られた協力者のLINE IDを取得する
   * @param int $questionIndex 質問Index
   * @return array assigneeIds: string[] 協力者のLINE ID配列
   */
  public function getAssignedStudent($questionIndex){
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文
      $stmt = $pdo -> prepare(
        "SELECT `userUid`
        FROM `Assignments` 
        WHERE `questionIndex` = :questionIndex"
      );
      //データの紐付け
      $stmt->bindValue(':questionIndex', $questionIndex, PDO::PARAM_INT);
      // 実行
      $res = $stmt->execute();

      if(!$res){
        throw new Exception('pdo not response');
      }else{
        $assigneeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return [ "assigneeIds" => $assigneeIds ];
      }
    } catch(PDOException $error){
      $this -> code = 500;
      return [
        "error" => [
          "type" => "pdo_exception",
          "message" => $error->getMessage()
        ],
      ];
    } finally{
      //echo "finally!!";
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
    if(!array_key_exists("userIdToken",$post)){
      $this->code = 400;
      return ["error" => [
        "type" => "invalid_param"
      ]];
    }
    try{
      $verifyResult = $this->verifyLine($post["userIdToken"]);
      $userId = $verifyResult["sub"];
    }catch(Exception $error){
      $this -> code = $error->getCode() || 500;
      return ["error" => json_decode($error->getMessage(),true)];
    }

    switch($args[0]){
      // 学生を実験協力者としてDBのリストに追加する
      case "registration":
        // パラメータの存在確認
        if(!array_key_exists("canAnswer",$post) ||
          !array_key_exists("age",$post) ||
          !array_key_exists("gender",$post)){
          $this->code = 400;
          return ["error" => [
            "type" => "invalid_param"
          ]];
        }
        $name = array_key_exists("name",$post) ? $post["name"] : null;
        $type = array_key_exists("type",$post) ? $post["type"] : "student";
        return $this->newUserRegistration($userId, $name, $type, $post["canAnswer"], $post["age"], $post["gender"]);
        break;

      // 無効なアクセス
      default:
        $this -> code = 400;
        return ["error" => [
          "type" => "invalid_access"
        ]];
    }
  }

  /**
   * 実験同意者のリストにユーザを追加
   * @param string $lineId ユーザのLINE固有id
   * @param array $questionnaire アンケートへの回答
   * @return array $result DB追加の成功/失敗
   */
  private function newUserRegistration($lineId, $name, $userType, $canAnswer, $age, $gender) {
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      $stmt = $pdo -> prepare(
        "INSERT INTO Users (userUid, name, type, canAnswer, age, gender)
        VALUES (:userUid, :name, :type, :canAnswer, :age, :gender)"
      );
      //データの紐付け
      // TODO: php7
      $stmt->bindValue(':userUid', $lineId, PDO::PARAM_STR);
      $stmt->bindValue(':name', $name, PDO::PARAM_STR);
      $stmt->bindValue(':type', $userType, PDO::PARAM_STR);
      $stmt->bindValue(':canAnswer', $canAnswer, PDO::PARAM_INT);
      $stmt->bindValue(':age', $age, PDO::PARAM_INT);
      $stmt->bindValue(':gender', $gender , PDO::PARAM_STR);
      // 実行
      $res = $stmt->execute();
      $lastIndex = $pdo->lastInsertId();
      if($res){
        $this->code = 201;
        //header("Location: ".$this->url.$lastIndex);
        return [
          "userUid" => $lineId,
          "type" => $userType,
          "canAnswer" => $canAnswer,
        ];
      }else{
        $this->code = 500;
        return ["error" => [
          "type" => "pdo_not_response"
        ]];
      }

    } catch(PDOException $error){
      $this -> code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    }
  }


  /**
   * 質問の回答者を割り振り，DBに登録する
   * @param string $questionerId 質問者のLINEid
   * @param int $questionIndex 質問のインデックス
   */
  public function assignStudentAnswerer($questionerId, $questionIndex){
    $db = new DB();
    $pdo = $db -> pdo();

    try{
      // mysqlの実行文の記述
      $stmtA = $pdo -> prepare(
        "SELECT `userUid`
        FROM `Users`
        WHERE `userUid` != :questionerId AND `type` = 'student' AND `canAnswer` = 1
        ORDER BY RAND() LIMIT 10"
      );
      
      //データの紐付け
      $stmtA->bindValue(':questionerId', $questionerId, PDO::PARAM_STR);
      // 実行
      $res = $stmtA->execute();
      if(!$res) throw new Exception("fail to assign student answerer");
      $assignedStudents = $stmtA->fetchAll(PDO::FETCH_COLUMN);

      $sqlB = "INSERT INTO `Assignments` (`questionIndex`, `userUid`)
              VALUES ";
      foreach(array_keys($assignedStudents) as $key){
        $sqlB .= "(:questionIndex".$key.", :studentId".$key."),";
      }
      $sqlB = substr($sqlB, 0, -1);
      $stmtB = $pdo -> prepare($sqlB);

      foreach($assignedStudents as $key => $studentId){
        $stmtB->bindValue(':questionIndex'.$key, $questionIndex, PDO::PARAM_INT);
        $stmtB->bindValue(':studentId'.$key, $studentId, PDO::PARAM_STR);
      }

      $res = $stmtB->execute();
      if(!$res) throw new Exception("fail to assign student answerer");

      $this->code = 201;
      return [
        "assignedStudents" => $assignedStudents
      ];

    } catch(PDOException $error){
      $this -> code = 500;
      return ["error" => [
        "type" => "pdo_exception",
        "message" => $error
      ]];
    } catch(Exception $error){
      $this -> code = 500;
      return ["error" => [
        "type" => "exception",
        "message" => $error->getMessage()
      ]];
    }
  }


  /**************************************************************************** */
  public function options()
  {
    header("Access-Control-Allow-Methods: OPTIONS,GET,HEAD,POST,PUT,DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    return [];
  }

  private function is_set($value)
  {
    return !(is_null($value) || $value === "");
  }

  public function verifyLine($id_token){
    //  Initiate curl session
    $ch = curl_init();

    $url = 'https://api.line.me/oauth2/v2.1/verify';

    $data = [
      'id_token' => $id_token, // LIFFから送信されたIDトークン
      'client_id' => getenv("LINE_CLIENT_ID"), // LIFFアプリを登録したLINEログインチャネルのチャネルID
    ];

    // Set the url
    curl_setopt($ch, CURLOPT_URL, $url);
    // Will return the response, if false it prints the response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the session and store the contents in $response
    $response = curl_exec($ch);
    // Closing the session
    curl_close($ch);

    $result = json_decode($response, true);

    if(array_key_exists("error", $result)){
      throw new Exception(json_encode([ 
        "status" => 400, 
        "error" => [
          "error" => $result["error"],
          "message" => $result["error_description"]
        ],
      ]));
    }else if(!array_key_exists("sub", $result)){
      throw new Exception(json_encode([ 
        "status" => 500, 
        "error" => [
          "error" => "invalid_response",
          "message" => json_encode($result)
        ],
      ]));
    }
    return $result;
  }
}