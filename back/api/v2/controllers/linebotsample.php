<?php 
error_log("hogehoge". "\n", 3, dirname(__FILE__).'/debugA.log');
$accessToken = '"H+Nx1Dt9Lq7Y9iI6rSJlZrUgo8zuEhOOERGiEz3WPYnXfljQ5nvjqQR+gSHsSfZ758i/S42h1Hk2A9inQBLUckP/2FVEI9HL8kwGGdd5+F/Kt3V9A3uuhMujnMLFg+7ai017gTOVpDZXSnnkeSPQHwdB04t89/1O/w1cDnyilFU="'; //ここにアクセストークンをコピペ　
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
$replyToken = $json_object->{"events"}[0]->{"replyToken"};
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};
$return_message_text = "こんにちは"; //返信する内容
sending_messages($accessToken, $replyToken, $message_type, $return_message_text);

function sending_messages($accessToken, $replyToken, $message_type, $return_message_text){
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}
?>