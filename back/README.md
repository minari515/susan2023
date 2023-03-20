# susan-next/back

DB 操作を主目的とした API サーバー

本番環境の都合上 PHP5.6 で実装中．
良い子は PHP7.0< の環境で動かそう．そして直そう，

PHP なら最近は Laravel とかが人気？
Goでリプレイスしてくれるツワモノ求む，

## How to use

### set .env

- DB(MySQL)の接続情報
- LIFF チャネル ID(client ID)

  - トークン検証に必要

- api/v○/.htaccess
  - api/v○/\* 以降へのアクセスを api/v○/index.php へリダイレクトさせるために必要
  - 開発環境と本番環境で変更が必要な可能性が高いので注意
  - sample を置いておくので 14 行目をよしなに修正 → api/v○/ へ配置

### start developing

```
docker-compose up -d back
```

環境変数を変更した場合は再ビルドさせる

```
docker-compose up --build --force-recreate -d
```
