# COACHTECH　お問合せフォーム
## 概要
- 保留
## ER図
![ER図](docs/ER.drawio.png)
## 環境構築手順
### リポジトリのクローン
- git clone https://github.com/tasuku1209/CONTACT-FORM-APP.git
### プロジェクトリポジトリへ移動
- cd CONTACT-FORM-APP
### .envファイル作成
- cp .env.example .env
### Dockerコンテナ起動
- ./vendor/bin/sail up -d
### Composerパッケージインストール
- ./vendor/bin/sail composer install
### アプリケーションキー生成
- ./vendor/bin/sail artisan key:generate
### マイグレーション実行
- ./vendor/bin/sail artisan migrate:fresh --seed
### アクセスURL
- http://localhost
## 使用技術
- PHP 8.x
- laravel 10.x
- MyAQL8.0
- Nginx
- Docker
- 以降、保留
## APIエンドポイント一覧
- 保留
## 開発環境URL
- http://localhost
## 作成者
- 高津　丞（たかつ　たすく）