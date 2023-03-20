# susan-next/front

SUSAN bot に連携する LIFF アプリケーション

URL: ???

## How to use

### set .env

ローカル(Docker)環境で動作確認

- .env, .env.develop ファイルを作成．
- (作成方法は各 sample ファイルを参照)
- LINE ログイン時の token をよしなに取得してきて(本番環境にアクセスしたり)，環境変数に設定するとそれっぽく動かせる．

本番環境(Vercel 想定)

- .env, .env.production ファイルの値をコンソールから設定
- 自前サーバーでは env ファイルを参照させる

### start developing

```
docker-compose up -d front
```

環境変数を変更した場合は再ビルドさせる

```
docker-compose up --build --force-recreate -d
```

### Enter to Docker shell

新しいライブラリなど入れるときは必ず Docker コンテナ内のシェルから実行．

```
## frontコンテナ内のシェルを起動
docker-compose run --rm front sh

## コンテナ内でパッケージインストールしたりなんやかんや
/home/app # yarn add ********

## コンテナから出る
/home/app # exit

(## ローカルディレクトリにパッケージを反映させる)
yarn install
```
