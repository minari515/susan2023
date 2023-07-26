<?php
use GuzzleHttp\Client;

// class gptreply
// {
function makereply($event)
{
  // 初期メッセージを格納
  $generatedText = "すいません，よくわかりませんでした🤔";
  // 自動回答判定フラグ
  $autoreply_flag = false;
  // urlを指定
  $apiUrl = 'https://api.openai.com/v1/chat/completions';
  // GPTによる質問のジャンル分け
  $data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
      ['role' => 'system', 'content' =>
      "あなたは送られてきた質問をカテゴリ別に分類する人です.\
      送られる質問に対し，カテゴリ名のみを返してください．\
      \
      制約条件：\
      ＊返信する内容はカテゴリ名のみで行ってください\
      ＊chatbotの自身を示す一人称は，私です\
      \
      カテゴリ名：\
      ＊chatbotシステムに関する質問\
      ＊授業に関する質問\
      ＊課題に関する質問\
      ＊エラーに関する質問\
      ＊データの前処理に関する質問\
      ＊プログラム自体に関する質問\
      \
      回答の例：\
      ＊chatbotシステムに関する質問\
      ＊授業に関する質問\
      ＊課題に関する質問\
      ＊エラーに関する質問\
      ＊データの前処理に関する質問\
      ＊プログラム自体に関する質問\
      ",
      ],
      ['role' => 'assistant', 'content' => $event->getText()],
      ['role' => 'user', 'content' => 'この質問内容に相当するカテゴリを返してください'],
    ],
    'max_tokens' => 500,
  ];

  // Guzzleを使ってAPIにリクエストを送信する
  $client = new Client();
  $gptresponse = $client->post($apiUrl, [
  'headers' => [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . getenv("OPENAI_API_KEY"),
    ],
  'json' => $data,
  ]);

  // APIからのレスポンスを取得する
  $result = json_decode($gptresponse->getBody()->getContents(), true);
  // 生成されたテキストを取得する
  $generatedText = $result['choices'][0]['message']['content'];
  
  if (preg_match("/プログラム自体に関する質問/", $generatedText) || preg_match("/データの前処理に関する質問/", $generatedText) || preg_match("/エラーに関する質問/", $generatedText))
  {
    $autoreply_flag = True;
  }
  if ($autoreply_flag) {
    $data = [
      'model' => 'gpt-3.5-turbo',
      'messages' => [
        ['role' => 'system', 'content' =>
        "あなたは教師であり，学生の質問に回答する必要があります．\
        また，本授業は，R言語を用いた情報系の講義であり，\
        主にデータサイエンスに関する分野を扱っています．\
        以上を踏まえた上で，送られた質問に対し\
        適切な回答を生成してください．\
        \
        質問の解答はR言語を前提として生成してください．\
        \
        質問の中には回答するにあたって情報が不十分な場合があります．\
        その際は情報が足りない旨を伝え，\
        回答するために必要な情報を追記するように催促してください．",
        ],
        ['role' => 'assistant', 'content' => $event->getText()],
        ['role' => 'user', 'content' => 'この質問内容への解答を返してください'],
      ],
      'max_tokens' => 500,
    ];
    // Guzzleを使ってAPIにリクエストを送信する
    $client = new Client();
    $gptresponse = $client->post($apiUrl, [
    'headers' => [
      'Content-Type' => 'application/json',
      'Authorization' => 'Bearer ' . getenv("OPENAI_API_KEY"),
      ],
    'json' => $data,
    ]);
    // APIからのレスポンスを取得する
    $result = json_decode($gptresponse->getBody()->getContents(), true);
    // 生成されたテキストを取得する
    $generatedText = $result['choices'][0]['message']['content'];
  } else {
    $generatedText = "先生に聞いてみようか🤔";
  }

  // デバッグ
  error_log(print_r($result, true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
  error_log(print_r($generatedText, true) . "\n", 3, dirname(__FILE__).'/debug_event.log');
  return $generatedText;
}
