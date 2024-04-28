<?php
require_once(dirname(__FILE__)."/../../../repository/UsersRepository.php");
require_once(dirname(__FILE__)."/../../../repository/AssignmentsRepository.php");
require_once(dirname(__FILE__)."/../../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__."/../../"); //.envを読み込む
$dotenv->load();

class UserService {

  private $usersRepository;
  private $assignmentsRepository;

  public function __construct() {
    $this->usersRepository = new UsersRepository();
    $this->assignmentsRepository = new AssignmentsRepository();
  }

  /**
   * LINEのIDトークン検証
   * @see https://developers.line.biz/ja/reference/line-login/#verify-id-token
   * @param string $id_token LINEのIDトークン
   */
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

  /**
   * 質問者を除いて、回答可能なユーザを取得する
   * @param int $questionerId 質問者のID
   * @return array 回答可能なユーザのID
   */
  public function getAssignableUsers($questionerId) {
    return $this->usersRepository->findAnswerableUsersForRandomExclude($questionerId);
  }

  /**
   * 回答協力者を登録する
   * @param array $assignerIds 協力者のID
   * @param int $questionIndex 質問のインデックス
   */
  public function assignAnswerers($assignerIds, $questionIndex) {
    return $this->assignmentsRepository->saveAssigners($assignerIds, $questionIndex);
  }
}
