# DrugStore向け在庫管理システム
<img width="1226" height="664" alt="Image" src="https://github.com/user-attachments/assets/38cf838d-eab1-4b7e-b931-54b156646ed3" />

## アプリ概要
- 医薬品卸倉庫を想定した在庫管理SPAのバックエンド
- 商品管理、入庫、出庫、在庫確認、履歴管理、スタッフ管理を行う

## 開発目的

- 在庫管理業務の効率化
- admin / staff の役割を分けた運用
- 入出庫ミス（数量・棚）の削減
- 在庫状況の可視化
- 実務に近いアプリケーション開発スキルの習得

## アプリケーションURL

ローカル環境：http://localhost:8000/

## 機能一覧

- adminログイン
- staffログイン
- 初回ログイン時のPIN変更
- 商品管理 CRUD
- 入庫機能
- 出庫機能
- 在庫一覧表示（ロケーション単位）＊将来アラート表示予定
- 入出庫履歴表示
- CSV出力
- スタッフ管理（スタッフ登録、スタッフログイン） 
- staff新規登録
- 社員番号自動採番
- 仮PIN自動生成　＊仮PIN⇒本PINへの変更は、今後調整あり。
- メール送信（MailHogで確認）
  
## DB設計、ER図

<img width="1175" height="431" alt="Image" src="https://github.com/user-attachments/assets/be9e93b4-30ad-47ef-be55-45e333aaf515" />

<img width="1163" height="469" alt="Image" src="https://github.com/user-attachments/assets/338ff17a-94de-4323-9c96-108207f2b404" />

<img width="1191" height="279" alt="Image" src="https://github.com/user-attachments/assets/251e3e62-bb60-408f-92b3-163a2605eb7d" />


<img width="2557" height="3754" alt="Image" src="https://github.com/user-attachments/assets/d360ce56-9939-4318-9dde-99c378cf6961" />

## 認証、権限

### admin
- メールアドレス + パスワードでログイン
- 商品管理、入庫、出庫、在庫確認、履歴確認、CSV出力、スタッフ管理を担当

### staff
- employee_code + PIN でログイン
- 初回ログイン時は PIN変更が必須
- PIN変更完了後に staff-dashboard へ遷移

### 補足
- `is_pin_changed` カラムにより PIN変更済み状態を管理
- `requires_pin_change` による遷移分岐を実装
- staff側の入出庫操作画面・権限制御は今後の拡張予定

## 使用技術

・Laravel 10

・nginx 1.21.1

・php 8.0

・mysql 8.0.26

・fortfy（laravel認証）

・Api/Sanctum　

・MailHog

・formrequest（laravelバリデーション）

## API概要
主なAPIの例：
- admin認証API
- staff認証API
- 商品管理API
- 入庫API
- 出庫API
- 在庫一覧API
- 入出庫履歴API
- スタッフ管理API

## 環境構築手順
### 1 Gitファイルをクローンする
 git clone https://github.com/shoyama1010/drugs-stock-app.git

### 2 Dockerコンテナを作成する
 cd drugs-stock-app

 docker compose up -d --build

### 3 Laravelパッケージをインストールする

 docker-compose exec php bash(PHPコンテナにログインし)

 cd src　（cd /var/www/srcのようにする）

### 4　Laravelの依存パッケージをインストール

　composer install

### 5 .envファイルを作成する
 cp .env.example .env

 .env のDB設定を以下のように確認する
 - DB_CONNECTION=mysql
 - DB_HOST=mysql
 - DB_PORT=3306
 - DB_DATABASE=drugstore
 - DB_USERNAME=laravel
 - DB_PASSWORD=secret

## 6 アプリケーションキーを生成

 php artisan key:generate   

### 7 テーブル及び初期データの作成

 php artisan migrate --seed


## テスト

本アプリでは主要機能について Feature Test を実装し、動作検証を行っています。

### 認証機能
- 管理者ログイン（email + password）：AdminLoginTest
- スタッフログイン（employee_code + PIN）：StaffLoginTest

それぞれについて、認証成功時にトークンおよびユーザー情報が正しく返却されることを確認

### 在庫管理機能
- 入庫処理：（StockInTest）
- 出庫処理：（StockOutTest）

以下の内容を確認
- 認証済ユーザーによる操作が可能であること
- 入出庫処理が正常に完了すること
- transactions テーブルに履歴が記録されること

### テスト実行方法

- テスト用DB作成（.env.testing）
- tests/Feature/配下に、各テスト用ファイル作成
- テスト用mysqlのため、マイグレーションを実行

  
- 各ファイルごとに、php artisan test

テストは `.env.testing` を用いて、本番DBと分離した環境で実行

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
