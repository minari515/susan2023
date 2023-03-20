# 🕊SUSAN-NEXT

講義における学生の質問行動を促進する授業支援チャットボットシステム SUSANbot

旧システムのリファクタリング版

# 🎬DEMO

- そのうち載せる

# 📝Features

LINE ボットを介して質問を匿名投稿ができ，ユーザが投稿した全質問を閲覧可能．

教員もこのシステムから回答することができる．

# 💫Requirement

## 💻Developing Environment

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

## 👀FrontEnd

### LINE bot

Next.js APIrouting を利用し，LINE bot の webhook を構築している

- [Next.js](https://nextjs.org/)
- [LINEbot SDK](https://github.com/line/line-bot-sdk-nodejs)
- [Dialogflow](https://cloud.google.com/dialogflow/es/docs)

### LIFF(web application)

- [Next.js](https://nextjs.org/)
- [LIFF](https://developers.line.biz/ja/docs/liff/overview/)
- [LIFF mock](https://github.com/line/liff-mock)

## 🗃BackEnd

疑似(?)REST API をバニラ PHP で構築している．

本番環境の都合上，PHP5.6 で組んでいるが，PHP7 以上の環境に書き直すことが求められるかと．

### Composer

環境変数(.env ファイル)を扱う関係で利用

# ⚙️Installation

## 🧩Create Each Web Service Accounts

- LINE ボット・LIFF アプリのためのチャネルを作成

  - [LINE Developers Console](https://developers.line.biz/console/) から作成
  - LINE ボット本体は Messaging API
  - LIFF アプリは LINE ログイン

- Dialogflow API を利用可能に
  - [Dialogflow ES](https://dialogflow.cloud.google.com/)でインテントなど設定
  - [Google Cloud Console](https://console.cloud.google.com/)から Dialogflow の API キーを発行

## 📝Set Environment File

- 各ディレクトリの.env.sample を参考に各サービスの環境変数を設定

## 🤖Start Developing

- 下記コマンドより Docker を起動．

```Shell
docker-compose build
docker-compose up -d
```

- (開発中ファイルの変更などがキャッシュビルドによって反映されない場合は以下のコマンドを使用)

```Shell
docker-compose up --build --force-recreate -d
```

- LIFF ページにアクセスできるか確認 → http://localhost:3000
  - トークン切れが発生する場合は，本番環境などにアクセスしてネットワークログから ID トークンを取得し，.env.development ファイルに記載する．
- 必要に応じて Docker コンテナ内のシェルからコマンドを実行

```Shell
docker-compose run --rm <<コンテナ名>> sh
```

> PHP5.6 での開発のため，VSCode の PHP validate のパスを docker コンテナ内を参照して設定するよう，php.sh を配置している．

# 🙋🏻‍♂️Author

- 作成者: 鈴木 舜也
- 所属: 和歌山大学大学院 吉野研究室
- E-mail: suzuki.shunya@g.wakayama-u.jp

# 🪪License

"SUSANbot" is Confidential.
