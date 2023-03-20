# Pages

## デフォルトコンポーネント

- ### [app](_app.tsx)

  全ページで共通の処理を行う．

  AuthProvider のラッパーによって，ログイン状態を保持する．

- ### [document](_document.tsx)

  全ページで共通の HTML を設定する．

  背景・テキストのカラーを設定している程度で，拡張などは今後自由に行うとよい．

## カスタムページコンポーネント

- ### [index](index.tsx)

  トップページ．
  実験同意の確認を行う．

- ### [questionsList](questionsList/index.tsx)

  質問一覧ページ．
  投稿質問を日時順に表示する．

  初期表示時には，30 件のみ取得・表示するが，スクロールによって追加で取得・表示する．

- ### [question](question/[questionId].tsx)

  質問ページ．
  質問のインデックスを指定し，その詳細を表示する．

  質問文と回答，追加の対話履歴を表示し，質問者・教員・回答協力者はテキスト・画像の投稿が可能．

- ### [howToUse](howToUse/index.tsx)

  使い方ページ．
  使い方を説明する．

- ### [questionnaire](questionnaire/index.tsx)

  実験評価アンケートページ．
  アンケートの説明と，回答フォームを表示する．

  アンケートの質問項目は json ファイルで管理している．

## API

LINE bot の応答を行う API．
LINE bot からのリクエストを受け取り，Dialogflow によって応答を生成し，LINE bot に返す．

### line

- #### [webhook](api/v1/line/webhook/index.ts)

  LINE bot の Webhook エンドポイント．
  ボットがメッセージを受信すると，LINE のサーバからこのエンドポイントに POST リクエストが送られる．

  イベントタイプ(メッセージ，友達登録...)や，メッセージタイプ(テキスト・スタンプ・画像...)によって処理を分岐し，それぞれにあった handlers を呼び出す．

- #### [push](api/v1/line/push/index.ts)

  ボット側からメッセージを送信するための API.
  質問や回答の更新をユーザに通知するために使用する．

  指定のユーザや登録者全体にメッセージを送信することができる．

- #### [libs](api/v1/line/libs)

  ちょっとした処理をまとめたモジュール．

  基本的な設定や，DB 通信や Flex メッセージのテンプレなど使いまわしできるものをまとめている．

### dialogflow

- #### [sessions](api/v1/dialogflow/sessions/index.ts)

  Dialogflow のセッションを管理する API．
  ボットへの入力メッセージを渡し，Dialogflow からの応答を受け取る．

- #### [intents](api/v1/dialogflow/intents/index.ts)

  Dialogflow のインテントを管理する API．インテントの取得(確認)・作成・更新が可能．
