<?php
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;

// /**
//  * 自動回答できない質問を教員に送信することを提案するメッセージ
//  * 
//  * @param string $QuestionText 入力された質問文
//  * @return FlexMessageBuilder $flexMessage 質問送信を促すFlexメッセージ
//  */
// function requestAnswerFlexMessageBuilder($QuestionText){
//   // バブル(１つのメッセージ)
//   $flexContainer = new BubbleContainerBuilder();

//   ####### Header要素 ##############
//   // 太字のテキスト要素を作る
//   $titleText = new TextComponentBuilder("🙋🏻‍♂️先生に質問してみよう！");
//   $titleText -> setWeight('bold');
//   $titleText -> setSize('lg');
//   $titleText -> setColor('#FFFFFF');

//   // 通常のテキスト要素を作る
//   $planeText1 = new TextComponentBuilder("まだ誰もしていない質問です🥳");
//   $planeText1 -> setWrap(true);
//   $planeText1  -> setColor('#FFFFFF');

//   $planeText2 = new TextComponentBuilder("匿名で送信して先生に答えてもらいましょう！");
//   $planeText2 -> setWrap(true);
//   $planeText2  -> setColor('#FFFFFF');

//   // 先程のテキスト要素を垂直に並べてボックスに格納
//   $headerBox = new BoxComponentBuilder('vertical',[$titleText, $planeText1, $planeText2]);
//   $headerBox->setBackgroundColor("#284275");

//   // バブルのHeader要素にセット
//   $flexContainer -> setHeader($headerBox);

//   ####### Body要素 ##############
//   // テキスト要素を作る
//   $captionText = new TextComponentBuilder("入力された質問文");
//   $captionText -> setSize('xs');
//   $captionText -> setAlign("center");
//   $captionText -> setColor("#B4B4B4");
//   $captionText -> setOffsetBottom("md");

//   // 通常のテキスト要素を作る
//   $mainText = new TextComponentBuilder($QuestionText);
//   $mainText -> setWrap(true);

//   // 先程のテキスト要素を垂直に並べてボックスに格納
//   $bodyBox = new BoxComponentBuilder('vertical',[$captionText, $mainText]);

//   // バブルのBody要素にセット
//   $flexContainer -> setBody($bodyBox);

//   ####### Footer要素 ################
//   // ボタンを押した際のアクションを設定
//   $sendAction = new MessageTemplateActionBuilder('このまま先生に送る', "質問を送信");
//   $rewriteAction = new MessageTemplateActionBuilder('書き直す', "書き直す");
//   $cancelAction = new MessageTemplateActionBuilder('質問しない', "キャンセル");

//   // ボタンを作ってアクションを載せる
//   $sendButton = new ButtonComponentBuilder($sendAction);
//   $rewriteButton = new ButtonComponentBuilder($rewriteAction);
//   $cancelButton = new ButtonComponentBuilder($cancelAction);

//   // ボタンを水平に並べてボックスに格納
//   $footerBox = new BoxComponentBuilder('vertical',[$sendButton, $rewriteButton, $cancelButton]);

//   $flexContainer -> setFooter($footerBox);

//   ########### ビルドして完成 ##################
//   // FlexMessageとしてビルド
//   $flexMessage = new FlexMessageBuilder('先生に質問してみますか？',$flexContainer);

//   return $flexMessage;
// }


/**
 * 教員に送信する直前の確認メッセージ
 * 
 * @param string $QuestionText 入力された質問文
 * @return FlexMessageBuilder $flexMessage 入力質問の確認と操作アクションを載せたflexメッセージ
 */
function checkSendAnswerFlexMessageBuilder($QuestionText){
  // バブル(１つのメッセージ)
  $flexContainer = new BubbleContainerBuilder();

  ####### Header要素 ##############
  // 太字のテキスト要素を作る
  $titleText = new TextComponentBuilder("🙋🏻‍♂️この質問を先生に送る？");
  $titleText -> setWeight('bold');
  $titleText -> setSize('lg');
  $titleText  -> setColor('#FFFFFF');

  // 通常のテキスト要素を作る
  $planeText = new TextComponentBuilder("この質問文で間違いないですか？");
  $planeText -> setWrap(true);
  $planeText  -> setColor('#FFFFFF');

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $headerBox = new BoxComponentBuilder('vertical',[$titleText, $planeText]);
  $headerBox->setBackgroundColor("#284275");

  // バブルのHeader要素にセット
  $flexContainer -> setHeader($headerBox);

  ####### Body要素 ##############
  // テキスト要素を作る
  $captionText = new TextComponentBuilder("入力された質問文");
  $captionText -> setSize('xs');
  $captionText -> setAlign("center");
  $captionText -> setColor("#B4B4B4");
  $captionText -> setOffsetBottom("md");

  // 通常のテキスト要素を作る
  $mainText = new TextComponentBuilder($QuestionText);
  $mainText -> setWrap(true);

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $bodyBox = new BoxComponentBuilder('vertical',[$captionText, $mainText]);

  // バブルのBody要素にセット
  $flexContainer -> setBody($bodyBox);

  ####### Footer要素 ################
  // ボタンを押した際のアクションを設定
  $sendAction = new MessageTemplateActionBuilder('この質問を先生に送る', "質問を送信");
  $rewriteAction = new MessageTemplateActionBuilder('書き直す', "書き直す");
  $cancelAction = new MessageTemplateActionBuilder('質問しない', "キャンセル");

  // ボタンを作ってアクションを載せる
  $sendButton = new ButtonComponentBuilder($sendAction);
  $rewriteButton = new ButtonComponentBuilder($rewriteAction);
  $cancelButton = new ButtonComponentBuilder($cancelAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$sendButton, $rewriteButton, $cancelButton]);

  $flexContainer -> setFooter($footerBox);

  ########### ビルドして完成 ##################
  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('この質問を先生に送る？',$flexContainer);

  return $flexMessage;
}

/**
 * 質問投稿の際に送る確認・詳細ページへのリンク付きメッセージ
 * @param int $index 質問のインデックス
 */
function sentQuestionFlexMessage($index){
  ####### Header要素 ##############
  $title = new TextComponentBuilder("🕊 送信したよ！");
  $title -> setSize('xl');
  $title -> setWeight('bold');
  $title -> setAlign('center');
  $title -> setColor('#FFFFFF');

  $HeaderBox = new BoxComponentBuilder('vertical',[$title]);
  $HeaderBox -> setPaddingAll('lg');
  $HeaderBox -> setBackgroundColor('#284275');

  ####### Body要素 ##############
  $text1 = new TextComponentBuilder("回答まで時間がかかるかもしれないけど待っててね");
  $text1 -> setAlign('start');
  $text1 -> setWrap(true);

  $text2 = new TextComponentBuilder("回答があれば通知します！");
  $text2 -> setAlign('start');
  $text2 -> setWrap(true);

  $text3 = new TextComponentBuilder("補足説明や画像は「質問の詳細へ」から追加できますよ！");
  $text3 -> setAlign('start');
  $text3 -> setWrap(true);

  $BodyBox = new BoxComponentBuilder('vertical',[$text1, $text2, $text3]);
  $BodyBox ->setJustifyContent('center');

  ####### Footer要素 ##############
  $actionUrl = 'https://susan2023-five.vercel.app/question/'.$index;
  $askAction = new UriTemplateActionBuilder('質問の詳細へ', $actionUrl);

  // ボタンを作ってアクションを載せる
  $askButton = new ButtonComponentBuilder($askAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$askButton]);

  #### バブル(１つのメッセージ)作成 ####
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setHeader($HeaderBox);
  $flexContainer -> setBody($BodyBox);
  $flexContainer -> setFooter($footerBox);

  ########### ビルドして完成 ##################
  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('質問を先生に送信しました！',$flexContainer);

  return $flexMessage;
}


/**
 * 他の学生が行った質問を表示するメッセージ
 * 
 * @param object $Questions DBに記録された直近の質疑応答データ
 */
function othersQuestionsFlexMessage($Questions){
  /* 最新4件の質疑応答情報のFlexMessage */
  foreach($Questions as $index => $Question){
    ####### Header要素 ##############
    // 質問のタイトル
    $q_title_jp = new TextComponentBuilder("🤔質問");
    $q_title_jp -> setSize('lg');
    $q_title_jp -> setWeight('bold');
    $q_title_jp -> setAlign('center');
    $q_title_jp  -> setColor('#FFFFFF');

    $q_title_en = new TextComponentBuilder('Question');
    $q_title_en -> setSize('xxs');
    $q_title_en -> setWeight('bold');
    $q_title_en -> setAlign('center');
    $q_title_en  -> setColor('#FFFFFF');

    $HeaderBox = new BoxComponentBuilder('vertical',[$q_title_jp, $q_title_en]);
    $HeaderBox -> setPaddingBottom('lg');
    $HeaderBox -> setBackgroundColor('#284275');

    ####### Body要素 ##############
    // 質問文
    $q_text = new TextComponentBuilder($Question['QuestionText']);
    $q_text -> setSize('lg');
    $q_text -> setWeight('bold');
    $q_text -> setWrap(true);
    $q_text  -> setColor('#FFFFFF');

    $BodyBox = new BoxComponentBuilder('vertical',[$q_text]);
    $BodyBox -> setPaddingBottom('lg');
    $BodyBox -> setBackgroundColor('#284275');

    ####### Footer要素 ##############
    // ボタンを押した際のアクションを設定
    $actionUrl = 'https://susan2023-five.vercel.app/question/'.$index;
    $askAction = new UriTemplateActionBuilder('詳細を見る', $actionUrl);

    // ボタンを作ってアクションを載せる
    $askButton = new ButtonComponentBuilder($askAction);

    // ボタンを垂直に並べてボックスに格納
    $footerBox = new BoxComponentBuilder('vertical',[$askButton]);

    #### バブル(１つのメッセージ)作成 ####
    $flexContainer = new BubbleContainerBuilder();
    $flexContainer -> setHeader($HeaderBox);
    $flexContainer -> setBody($BodyBox);
    $flexContainer -> setFooter($footerBox);

    #### カルーセルにセット ####
    $bubbles[] = $flexContainer;
  }

  /* 質疑情報一覧をLIFFで表示するFlexMessage */
  ####### Header要素 ##############
  $title = new TextComponentBuilder("🧐もっと見る");
  $title -> setSize('xl');
  $title -> setWeight('bold');
  $title -> setAlign('center');
  $title -> setColor('#FFFFFF');
  $title -> setOffsetTop('lg');

  $HeaderBox = new BoxComponentBuilder('vertical',[$title]);
  $HeaderBox -> setPaddingAll('lg');
  $HeaderBox -> setBackgroundColor('#284275');

  ####### Body要素 ##############
  $text1 = new TextComponentBuilder("質問を一覧で確認できます");
  $text1 -> setAlign('start');
  $text1 -> setWrap(true);
  $text1  -> setColor('#FFFFFF');

  $text2 = new TextComponentBuilder("回答待ちの質問も見てみましょう");
  $text2 -> setAlign('start');
  $text2 -> setWrap(true);
  $text2  -> setColor('#FFFFFF');

  $BodyBox = new BoxComponentBuilder('vertical',[$text1, $text2]);
  $BodyBox ->setJustifyContent('center');
  $BodyBox -> setBackgroundColor('#284275');

  ####### Footer要素 ##############
  $actionUrl = 'https://susan2023-five.vercel.app/questionsList';
  $askAction = new UriTemplateActionBuilder('質問一覧を開く', $actionUrl);

  // ボタンを作ってアクションを載せる
  $askButton = new ButtonComponentBuilder($askAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$askButton]);

  #### バブル(１つのメッセージ)作成 ####
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setHeader($HeaderBox);
  $flexContainer -> setBody($BodyBox);
  $flexContainer -> setFooter($footerBox);

  #### カルーセルにセット ####
  $bubbles[] = $flexContainer;


  $carousel = new CarouselContainerBuilder($bubbles);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('最近された質問だよ！',$carousel);

  return $flexMessage;
}


/**
 * 自動回答メッセージ
 * 
 * @param int $QuestionIndex DBに登録されている質問のインデックス
 * @param string $QuestionText DBに登録されている質問文
 * @param string $AnswerText DBに登録されている回答文
 */
function QandAFlexMessage($QuestionIndex, $QuestionText, $AnswerText){
  ####### Header要素 ##############
  // 質問のタイトル
  $q_title_jp = new TextComponentBuilder("🤔質問");
  $q_title_jp -> setSize('lg');
  $q_title_jp -> setWeight('bold');
  $q_title_jp -> setAlign('center');
  $q_title_jp  -> setColor('#FFFFFF');

  $q_title_en = new TextComponentBuilder('Question');
  $q_title_en -> setSize('xxs');
  $q_title_en -> setWeight('bold');
  $q_title_en -> setAlign('center');
  $q_title_en  -> setColor('#FFFFFF');

  $q_titleBox = new BoxComponentBuilder('vertical',[$q_title_jp, $q_title_en]);
  $q_titleBox -> setPaddingBottom('lg');

  // 質問文
  $q_text = new TextComponentBuilder($QuestionText);
  $q_text -> setSize('lg');
  $q_text -> setWeight('bold');
  $q_text -> setWrap(true);
  $q_text  -> setColor('#FFFFFF');

  // 質問エリア
  $HeaderBox = new BoxComponentBuilder('vertical',[$q_titleBox, $q_text]);
  $HeaderBox -> setBackgroundColor('#284275');

  ####### Body要素 ##############
  // 回答のタイトル
  $a_title_jp = new TextComponentBuilder("💡回答");
  $a_title_jp -> setSize('lg');
  $a_title_jp -> setWeight('bold');
  $a_title_jp -> setAlign('center');

  $a_title_en = new TextComponentBuilder('Answer');
  $a_title_en -> setSize('xxs');
  $a_title_en -> setWeight('bold');
  $a_title_en -> setAlign('center');

  $a_titleBox = new BoxComponentBuilder('vertical',[$a_title_jp, $a_title_en]);
  $a_titleBox -> setPaddingBottom('lg');

  // 回答文
  $a_text = new TextComponentBuilder($AnswerText);
  $a_text -> setSize('lg');
  $a_text -> setWeight('bold');
  $a_text -> setWrap(true);

  // 回答エリア
  $BodyBox = new BoxComponentBuilder('vertical',[$a_titleBox, $a_text]);
  $BodyBox -> setBackgroundColor('#FFFFFF');

  ####### Footer要素 ##############
  // 「求める回答ではない」ボタンの設置
  $viewDetailAction =  new UriTemplateActionBuilder('詳しく見る', 'https://susan2023-five.vercel.app/question/'.$QuestionIndex);
  $thanksAction = new MessageTemplateActionBuilder('ありがとう！', "ありがとう！");
  $requestDifferentAnswerAction = new MessageTemplateActionBuilder('求めた回答でない', "求めていた回答ではありません");

  // ボタンを作ってアクションを載せる
  $viewDetailButton = new ButtonComponentBuilder($viewDetailAction);
  $thanksButton = new ButtonComponentBuilder($thanksAction);
  $requestDifferentAnswerButton = new ButtonComponentBuilder($requestDifferentAnswerAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$viewDetailButton, $thanksButton, $requestDifferentAnswerButton]);
  
  // FlexMessage１つ分(バブル)を作って先程のボックスをそれぞれBodyとFooterに格納
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setHeader($HeaderBox);
  $flexContainer -> setBody($BodyBox);
  $flexContainer -> setFooter($footerBox);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('回答が届きました！',$flexContainer);

  return $flexMessage;
}


/**
 * システムの使い方を表示するメッセージ 
 */
function howToUseFlexMessage(){
  $resorces[] = array('title' => '🤔質問してみよう', 'imgpath' => "https://www2.yoslab.net/~suzuki/susan/susan_bot/Assets/HowToUse-ImageMap-0.png");
  $resorces[] = array('title' => '👥皆の質問を見てみよう', 'imgpath' => 'https://www2.yoslab.net/~suzuki/susan/susan_bot/Assets/HowToUse-ImageMap-1.png');
  //$resorces[] = array('title' => '🙏要望を送ってみよう', 'imgpath' => 'https://www2.yoslab.net/~suzuki/susan/susan_bot/Assets/HowToUse-ImageMap-2.png');

  foreach($resorces as $resorce){
    ####### Header要素 ##############
    // 質問のタイトル
    $q_title_jp = new TextComponentBuilder($resorce['title']);
    $q_title_jp -> setSize('lg');
    $q_title_jp -> setWeight('bold');
    $q_title_jp -> setAlign('center');
    $q_title_jp  -> setColor('#FFFFFF');

    $HeaderBox = new BoxComponentBuilder('vertical',[$q_title_jp]);
    $HeaderBox -> setPaddingBottom('lg');
    $HeaderBox -> setBackgroundColor('#284275');
    ####### Hero要素 ##############
    $image = new ImageComponentBuilder($resorce['imgpath']);
    $image -> setUrl($resorce['imgpath']);
    $image -> setSize("full");

    #### バブル(１つのメッセージ)作成 ####
    $flexContainer = new BubbleContainerBuilder();
    $flexContainer -> setHeader($HeaderBox);
    $flexContainer -> setHero($image);

    #### カルーセルにセット ####
    $bubbles[] = $flexContainer;
  }
  
  $carousel = new CarouselContainerBuilder($bubbles);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('このチャットボットの使い方です！',$carousel);

  return $flexMessage;
}

function checkSendRequestFlexMessageBuilder($RequestText){
  // バブル(１つのメッセージ)
  $flexContainer = new BubbleContainerBuilder();

  ####### Header要素 ##############
  // 太字のテキスト要素を作る
  $titleText = new TextComponentBuilder("🙏この要望を先生に送る？");
  $titleText -> setWeight('bold');
  $titleText -> setSize('lg');
  $titleText  -> setColor('#FFFFFF');

  // 通常のテキスト要素を作る
  $planeText = new TextComponentBuilder("このメッセージで間違いないですか？");
  $planeText -> setWrap(true);
  $planeText  -> setColor('#FFFFFF');

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $headerBox = new BoxComponentBuilder('vertical',[$titleText, $planeText]);
  $headerBox->setBackgroundColor("#284275");

  // バブルのHeader要素にセット
  $flexContainer -> setHeader($headerBox);

  ####### Body要素 ##############
  // テキスト要素を作る
  $captionText = new TextComponentBuilder("入力されたメッセージ");
  $captionText -> setSize('xs');
  $captionText -> setAlign("center");
  $captionText -> setColor("#B4B4B4");
  $captionText -> setOffsetBottom("md");

  // 通常のテキスト要素を作る
  $mainText = new TextComponentBuilder($RequestText);
  $mainText -> setWrap(true);

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $bodyBox = new BoxComponentBuilder('vertical',[$captionText, $mainText]);

  // バブルのBody要素にセット
  $flexContainer -> setBody($bodyBox);

  ####### Footer要素 ################
  // ボタンを押した際のアクションを設定
  $sendAction = new MessageTemplateActionBuilder('この要望を先生に送る', "要望メッセージを先生に送信");
  $cancelAction = new MessageTemplateActionBuilder('送信しない', "キャンセル");

  // ボタンを作ってアクションを載せる
  $sendButton = new ButtonComponentBuilder($sendAction);
  $cancelButton = new ButtonComponentBuilder($cancelAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$sendButton, $cancelButton]);

  $flexContainer -> setFooter($footerBox);

  ########### ビルドして完成 ##################
  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('要望メッセージはこれでいい？',$flexContainer);

  return $flexMessage;
}

/**
 * 質問と回答を表示したFlexメッセージ(バブル)を生成する
 * @param string $QuestionText 質問文
 * @param string $AnswerText 回答文
 * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder $flexContainer
 */
function QandAFlexContainer($QuestionId, $QuestionText, $AnswerText){
  ####### Header要素 ##############
  // 質問のタイトル
  $q_title_jp = new TextComponentBuilder("🤔質問");
  $q_title_jp -> setSize('lg');
  $q_title_jp -> setWeight('bold');
  $q_title_jp -> setAlign('center');
  $q_title_jp  -> setColor('#FFFFFF');

  $q_title_en = new TextComponentBuilder('Question');
  $q_title_en -> setSize('xxs');
  $q_title_en -> setWeight('bold');
  $q_title_en -> setAlign('center');
  $q_title_en  -> setColor('#FFFFFF');

  $q_titleBox = new BoxComponentBuilder('vertical',[$q_title_jp, $q_title_en]);
  $q_titleBox -> setPaddingBottom('lg');

  // 質問文
  $q_text = new TextComponentBuilder($QuestionText);
  $q_text -> setSize('lg');
  $q_text -> setWeight('bold');
  $q_text -> setWrap(true);
  $q_text  -> setColor('#FFFFFF');

  // 質問エリア
  $HeaderBox = new BoxComponentBuilder('vertical',[$q_titleBox, $q_text]);
  $HeaderBox -> setBackgroundColor('#284275');

  ####### Body要素 ##############
  // 回答のタイトル
  $a_title_jp = new TextComponentBuilder("💡回答");
  $a_title_jp -> setSize('lg');
  $a_title_jp -> setWeight('bold');
  $a_title_jp -> setAlign('center');

  $a_title_en = new TextComponentBuilder('Answer');
  $a_title_en -> setSize('xxs');
  $a_title_en -> setWeight('bold');
  $a_title_en -> setAlign('center');

  $a_titleBox = new BoxComponentBuilder('vertical',[$a_title_jp, $a_title_en]);
  $a_titleBox -> setPaddingBottom('lg');

  // 回答文
  $a_text = new TextComponentBuilder($AnswerText);
  $a_text -> setSize('lg');
  $a_text -> setWeight('bold');
  $a_text -> setWrap(true);

  // 回答エリア
  $BodyBox = new BoxComponentBuilder('vertical',[$a_titleBox, $a_text]);
  $BodyBox -> setBackgroundColor('#FFFFFF');
  
  // FlexMessage１つ分(バブル)を作って先程のボックスをそれぞれBodyとFooterに格納
  $flexContainer = new BubbleContainerBuilder();
  $flexContainer -> setHeader($HeaderBox);
  $flexContainer -> setBody($BodyBox);

  $linkButtons = array();
  
  $qandaUrl = 'https://susan2023-five.vercel.app/question/'.$QuestionId;
  // urlリンクをボタンで表示
  $urlAction = new UriTemplateActionBuilder('詳細へ', $qandaUrl);
  // ボタンを作ってアクションを載せる
  $linkButton = new ButtonComponentBuilder($urlAction);
  $linkButtons[] = $linkButton;
  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical', $linkButtons);
  $flexContainer -> setFooter($footerBox);

  return $flexContainer;
}

// /**
//  * チャットを表示したFlexメッセージ(バブル)を生成する
//  * @param string $ChatText 回答文
//  * @return \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder $flexContainer
//  */
// function ChatFlexContainer($QuestionId, $ChatText){
//   ####### Header要素 ##############
//   // メッセージのタイトル
//   $q_title_jp = new TextComponentBuilder("💬新しいメッセージ");
//   $q_title_jp -> setSize('lg');
//   $q_title_jp -> setWeight('bold');
//   $q_title_jp -> setAlign('center');
//   $q_title_jp  -> setColor('#FFFFFF');

//   $q_titleBox = new BoxComponentBuilder('vertical',[$q_title_jp]);

//   // ヘッダーの設定
//   $HeaderBox = new BoxComponentBuilder('vertical',[$q_titleBox]);
//   $HeaderBox -> setBackgroundColor('#284275');

//   ####### Body要素 ##############
//   // メッセージ
//   $chat_text = new TextComponentBuilder($ChatText);
//   $chat_text -> setSize('lg');
//   $chat_text -> setWeight('bold');
//   $chat_text -> setWrap(true);

//   // ボディの設定
//   $BodyBox = new BoxComponentBuilder('vertical',[$chat_text]);
//   $BodyBox -> setBackgroundColor('#FFFFFF');
  
//   ####### Footer要素 ##############
//   $actionUrl = 'https://susan2023-five.vercel.app/question/'.$QuestionId;
//   // urlリンクをボタンで表示
//   $urlAction = new UriTemplateActionBuilder('詳細へ', $actionUrl);
//   // ボタンを作ってアクションを載せる
//   $linkButton = new ButtonComponentBuilder($urlAction);

//   // ボタンを垂直に並べてボックスに格納
//   $footerBox = new BoxComponentBuilder('vertical', [$linkButton]);

//   // FlexMessage１つ分(バブル)を作って先程のボックスをそれぞれHeaderとBodyに格納
//   $flexContainer = new BubbleContainerBuilder();
//   $flexContainer -> setHeader($HeaderBox);
//   $flexContainer -> setBody($BodyBox);
//   $flexContainer -> setFooter($footerBox);

//   return $flexContainer;
// }

/**
 * 自動回答できない質問を教員に送信することを提案するメッセージ
 * 
 * @param string $QuestionText 入力された質問文
 * @return FlexMessageBuilder $flexMessage 質問送信を促すFlexメッセージ
 */
function ChatFlexContainer($QuestionText){
  // バブル(１つのメッセージ)
  $flexContainer = new BubbleContainerBuilder();

  ####### Header要素 ##############
  // 太字のテキスト要素を作る
  $titleText = new TextComponentBuilder("botの回答です🤖");
  $titleText -> setWeight('bold');
  $titleText -> setSize('lg');
  $titleText -> setColor('#FFFFFF');

  // 通常のテキスト要素を作る
  $planeText1 = new TextComponentBuilder("回答を確認して質問が解決するか確かめてみてください！");
  $planeText1 -> setWrap(true);
  $planeText1  -> setColor('#FFFFFF');

  $planeText2 = new TextComponentBuilder("解決しなかった場合は先生に聞いてみよう🙋🏻‍♂️");
  $planeText2 -> setWrap(true);
  $planeText2  -> setColor('#FFFFFF');

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $headerBox = new BoxComponentBuilder('vertical',[$titleText, $planeText1, $planeText2]);
  $headerBox->setBackgroundColor("#284275");

  // バブルのHeader要素にセット
  $flexContainer -> setHeader($headerBox);

  ####### Body要素 ##############
  // テキスト要素を作る
  $captionText = new TextComponentBuilder("botの回答");
  $captionText -> setSize('xs');
  $captionText -> setAlign("center");
  $captionText -> setColor("#B4B4B4");
  $captionText -> setOffsetBottom("md");

  // 通常のテキスト要素を作る
  $mainText = new TextComponentBuilder($QuestionText);
  $mainText -> setWrap(true);

  // 先程のテキスト要素を垂直に並べてボックスに格納
  $bodyBox = new BoxComponentBuilder('vertical',[$captionText, $mainText]);

  // バブルのBody要素にセット
  $flexContainer -> setBody($bodyBox);

  // ####### Footer要素 ################
  // // ボタンを押した際のアクションを設定
  // $sendAction = new MessageTemplateActionBuilder('このまま先生に送る', "質問を送信");
  // $rewriteAction = new MessageTemplateActionBuilder('書き直す', "書き直す");
  // $cancelAction = new MessageTemplateActionBuilder('質問しない', "キャンセル");

  // // ボタンを作ってアクションを載せる
  // $sendButton = new ButtonComponentBuilder($sendAction);
  // $rewriteButton = new ButtonComponentBuilder($rewriteAction);
  // $cancelButton = new ButtonComponentBuilder($cancelAction);

  // // ボタンを水平に並べてボックスに格納
  // $footerBox = new BoxComponentBuilder('vertical',[$sendButton, $rewriteButton, $cancelButton]);

  // $flexContainer -> setFooter($footerBox);

  ########### ビルドして完成 ##################
  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('先生に質問してみますか？',$flexContainer);

  return $flexMessage;
}

/**
 * 友達登録時に送信するFlexメッセージの生成
 * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder $flexMessage
 */
function FollowFlexMessageBuilder(){
  $flexContainer = new BubbleContainerBuilder();

  ####### Header要素 ##############
  $title = new TextComponentBuilder("🥳登録ありがとう!");
  $title -> setSize('xl');
  $title -> setWeight('bold');
  $title -> setAlign('center');
  $title -> setColor('#FFFFFF');
  $title -> setOffsetTop('lg');

  $HeaderBox = new BoxComponentBuilder('vertical',[$title]);
  $HeaderBox -> setPaddingAll('lg');
  $HeaderBox -> setBackgroundColor('#284275');

  $flexContainer -> setHeader($HeaderBox);

  ####### Body要素 ##############
  $text1 = new TextComponentBuilder("はじめまして，SUSANbotです！");
  $text1 -> setAlign('start');
  $text1 -> setWrap(true);
  $text1 -> setColor('#FFFFFF');

  $text2 = new TextComponentBuilder("あなたの代わりに匿名で先生に質問を送信したり，先生からの回答をお知らせします！");
  $text2 -> setAlign('start');
  $text2 -> setWrap(true);
  $text2 -> setColor('#FFFFFF');

  $text3 = new TextComponentBuilder('まずはシステム利用への同意をお願いします！');
  $text3 -> setAlign('start');
  $text3 -> setWrap(true);
  $text3 -> setColor('#FFFFFF');

  $BodyBox = new BoxComponentBuilder('vertical',[$text1, $text2, $text3]);
  $BodyBox -> setJustifyContent('center');
  $BodyBox -> setSpacing('xl');
  $BodyBox -> setBackgroundColor('#284275');

  $flexContainer -> setBody($BodyBox);

  ####### Footer要素 ##############
  $actionUrl = 'https://susan2023-five.vercel.app';
  $askAction = new UriTemplateActionBuilder('実験同意ページを開く', $actionUrl);

  // ボタンを作ってアクションを載せる
  $askButton = new ButtonComponentBuilder($askAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$askButton]);

  $flexContainer -> setFooter($footerBox);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('まずはシステム利用に同意をお願いします！',$flexContainer);

  return $flexMessage;
}

/**
 * 評価アンケートを送信するFlexメッセージの生成
 * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder $flexMessage
 */
function askEvaluationFlexMessageBuilder(){
  $flexContainer = new BubbleContainerBuilder();

  ####### Header要素 ##############
  $title = new TextComponentBuilder("最後のお願いです！🙇🏻‍♂️");
  $title -> setSize('xl');
  $title -> setWeight('bold');
  $title -> setAlign('center');
  $title -> setColor('#FFFFFF');
  $title -> setOffsetTop('lg');

  $HeaderBox = new BoxComponentBuilder('vertical',[$title]);
  $HeaderBox -> setPaddingAll('lg');
  $HeaderBox -> setBackgroundColor('#284275');

  $flexContainer -> setHeader($HeaderBox);

  ####### Body要素 ##############
  $text1 = new TextComponentBuilder("実験にご参加いただき本当にありがとうございます!");
  $text1 -> setAlign('start');
  $text1 -> setWrap(true);
  $text1 -> setColor('#FFFFFF');

  $text2 = new TextComponentBuilder("最後に下のボタンからアンケートに回答いただくと，実験は終了となります．");
  $text2 -> setAlign('start');
  $text2 -> setWrap(true);
  $text2 -> setColor('#FFFFFF');

  $text3 = new TextComponentBuilder('あと少しだけご協力ください！');
  $text3 -> setAlign('start');
  $text3 -> setWrap(true);
  $text3 -> setColor('#FFFFFF');

  $text4 = new TextComponentBuilder('(アンケート画面が開かない場合は一度画面を閉じて，再度開き直してみてください)');
  $text4 -> setAlign('start');
  $text4 -> setWrap(true);
  $text4 -> setColor('#FFFFFF');

  $BodyBox = new BoxComponentBuilder('vertical',[$text1, $text2, $text3, $text4]);
  $BodyBox -> setJustifyContent('center');
  $BodyBox -> setSpacing('xl');
  $BodyBox -> setBackgroundColor('#284275');

  $flexContainer -> setBody($BodyBox);

  ####### Footer要素 ##############
  $actionUrl = 'https://www2.yoslab.net/~suzuki/susan/susan_liff/evaluateform';//すーさんに聞く
  $askAction = new UriTemplateActionBuilder('実験評価アンケート', $actionUrl);

  // ボタンを作ってアクションを載せる
  $askButton = new ButtonComponentBuilder($askAction);

  // ボタンを垂直に並べてボックスに格納
  $footerBox = new BoxComponentBuilder('vertical',[$askButton]);

  $flexContainer -> setFooter($footerBox);

  // FlexMessageとしてビルド
  $flexMessage = new FlexMessageBuilder('実験評価アンケートにご協力ください！',$flexContainer);

  return $flexMessage;
}