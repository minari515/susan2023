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

- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)

# ⚙️Installation

## 🧩Create Each Web Service Accounts

- LINE ボット・LIFF アプリのためのチャネルを作成

  - [LINE Developers Console](https://developers.line.biz/console/) から作成
  - LINE ボット本体は **Messaging API**
    - チャネルシークレット，チャネルアクセストークン(長期)をそれぞれ取得
  - LIFF アプリは **LINE ログイン**
    - LIFF タグから LIFF アプリを追加し，LIFF ID を取得

  ![スクリーンショット 2023-04-13 20 52 54](https://user-images.githubusercontent.com/52482280/231750418-d221fab5-4309-4256-a181-37f4accb2b89.png)  
  ▲ こんな感じ

- Dialogflow API を利用可能に

  - [Dialogflow ES](https://dialogflow.cloud.google.com/)でインテントなど設定
    - 既存のプロジェクトのインテントをエクスポート・インポートを行い引き継ぐこともできる．
  - Dialogflow ES の設定から General > Google Project > Project ID のリンクから [Google Cloud Console](https://console.cloud.google.com/)にアクセスし，Dialogflow の サービスアカウントキーを発行

    - 作成した Dialogflow プロジェクトの[サービスアカウント](https://console.cloud.google.com/projectselector2/iam-admin/serviceaccounts)からキーの作成(JSON)を行う．
    - JSON の中身を .env ファイルに記載する(後述)
      ![スクリーンショット 2023-04-13 21 00 45](https://user-images.githubusercontent.com/52482280/231752179-176a849e-a86a-4937-b98f-524381b86261.png)

  - GCP の[IAM](https://console.cloud.google.com/projectselector2/iam-admin/iam)から，サービスアカウントに対してアクセス権を付与する．
    - プリンシパルはサービスアカウントのメールアドレス．ロールは
      「Dialogflow API 管理者」を指定する
      ![スクリーンショット 2023-04-13 21 02 23](https://user-images.githubusercontent.com/52482280/231752678-e3020aa2-afc4-4661-8445-b1d47785b4c6.png)

## 📝Set Environment File

- 各ディレクトリの.env.sample を参考に各サービスの環境変数を .env , .env.development , .env.production に設定
- back/api の .htaccess を，自身の環境にあわせてリダイレクト先(index.php)のパスを指定
  - これを直していないと CORS エラーにもなるので注意

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

- back で composer install を実行

```Shell
docker-compose run --rm back sh # コンテナ内のシェルに入る
composer install
...
exit # composer install が完了次第，コンテナから出る
```

- LIFF ページにアクセスできるか確認 → http://localhost:3000
  - トークン切れが発生する場合は，本番環境などにアクセスして Developer Tool など用いてネットワークログから ID トークンを取得し，.env.development ファイルに記載する．
- 必要に応じて Docker コンテナ内のシェルからコマンドを実行

```Shell
docker-compose run --rm <<コンテナ名>> sh
```

> PHP5.6 での開発のため，VSCode の PHP validate のパスを docker コンテナ内を参照して設定するよう，php.sh を配置している．

## 🚀Deploy Hosting Server

### FrontEnd

[Vercel](https://vercel.com/)の利用を想定している．
GitHub を連携させておくと，push するたびに自動でデプロイされるので便利．

- Vercel の場合，Project Settings > Environment Variables から環境変数を設定する．(.env , .env.production を import するとよい)
- Dialogflow のプライベートキーは，Environment Variables に設定する際に改行コードを削除する必要があるため注意．

### BackEnd(Database)

研究室のサーバを利用することを想定している．

- back/.env の HOME_DIR に対応するディレクトリに配置
- back/api/vXX/.htaccess のリダイレクト先を環境に合わせて変更

### Service

LINE bot のエンドポイントを設定

- 作成した Messaging API のチャネルの Messaging API 設定 > Webhook 設定 > Webhook URL を設定
- `https://<<FrontServerURL(ex.Vercel)>>/api/vXX/line/webhook` など

LIFF アプリのコールバック・エンドポイント URL をそれぞれ設定

- 作成した LINE ログインのチャネルの LINE ログイン設定 から「ウェブアプリで LINE ログインを利用」のトグルをオンにする
- コールバック URL を設定

  - `https://<<FrontServerURL(ex.Vercel)>>/`など

- LIFF > LIFF アプリを追加し，作成した LIFF アプリの詳細からエンドポイント URL を設定
  - `https://<<FrontServerURL(ex.Vercel)` など
  - Scope は `openid` が必要

# 🙋🏻‍♂️Author

- 作成者: 鈴木 舜也
- 所属: 和歌山大学大学院 吉野研究室
- E-mail: suzuki.shunya@g.wakayama-u.jp

# 🪪License

"SUSANbot" is Confidential.
