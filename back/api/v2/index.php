<?php
ini_set('display_errors',1);

//header("Access-Control-Allow-Origin: https://susan-xi.vercel.app"); //本番用
header("Access-Control-Allow-Origin: http://localhost:3000"); //開発用

include(dirname( __FILE__)."/../database.php");

// URL(REQUEST_URI)と「このindex.phpがあるディレクトリのパス」を比較
preg_match(
  '|'.dirname($_SERVER["SCRIPT_NAME"]).'/([\w%/]*)|', 
  $_SERVER["REQUEST_URI"], 
  $matches
);

// "/v1/"以降の文字列をスラッシュ区切りした配列を格納
$paths = explode('/',$matches[1]);
// $paths[0]を$fileへ，$pathsは元の[1]以降の配列になる
$file = array_shift($paths);

// URLで指定されたルートに該当するファイルを呼び出す
$file_path = './controllers/'.$file.'.php';
if(file_exists($file_path)){
  //該当ファイルを読み込み
  include($file_path);
  $class_name = ucfirst($file)."Controller"; // $file(文字列)の最初の一文字を大文字へ変換+"Controller"(命名規則)
  $method_name = strtolower($_SERVER["REQUEST_METHOD"]); // get||post||put||delete||options
  $object = new $class_name(); // クラスのインスタンスの生成
  //$response = json_encode($object->$method_name(...$paths)); //TO DO: スプレッド構文はphp ver.5.6系で使えないので泣く泣く配列を渡すことに
  $response = json_encode($object->$method_name($paths)); // 該当コントローラにある$method_nameと同名のメソッドを呼び出し，その返り値をjsonとして$responseへ
  // 該当コントローラの決めたHTTPステータスコードを受け取る
  if(!$object->code){
    $response_code = 200;
  }else{
    $response_code = $object->code;
  };
  
  header("Content-Type: application/json; charset=utf-8", true, $response_code);
  echo $response;

}else{ //ファイルが無ければエラー処理
  header("HTTP/1.1 404 Not Found");
  exit;
} 