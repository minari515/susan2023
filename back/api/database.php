<?php

require_once(dirname(__FILE__)."/../vendor/autoload.php");
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__."/../"); //.envを読み込む
$dotenv->load();

/**
 * データベースに接続するために利用する
 */
class DB
{
  /**
   * データベース操作用オブジェクトを返す
   * @return mixed PHP Data Objects
   */
  function pdo()
  {
    $db_setting = [
      "dsn" => getenv("DB_DSN"),
      "user" => getenv("DB_USER"),
      "password" => getenv("DB_PASSWORD"),
    ];;

    try{
      $driver_option = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,// カラム名をキーとする連想配列で取得する
      ];
      $pdo = new PDO($db_setting["dsn"],$db_setting["user"],$db_setting["password"],$driver_option);

    }catch(PDOException $error){
      error_log(print_r($error, true)."\n", 3, dirname( __FILE__).'/debug.log');
      header("Content-Type: application/json; charset=utf-8", true, 500);
      echo json_encode(["error" => ["type" => "server_error","message"=>$error->getMessage()]]);
      die();
    }
    
    return $pdo;
  }
}