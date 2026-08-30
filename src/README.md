# DrugStore向け在庫管理アプリ
<img width="1226" height="664" alt="Image" src="https://github.com/user-attachments/assets/38cf838d-eab1-4b7e-b931-54b156646ed3" />

## 機能一覧

### 認証機能
- 管理者ログイン
- Laravel Sanctumを利用したAPI認証
- 認証済みユーザー情報の取得
- ログアウト
- 未認証ユーザーによる保護APIへのアクセス制限

### 商品管理
- 商品一覧表示
- 商品詳細表示
- 商品登録
- 商品編集
- 商品削除
- 商品コードや商品情報の管理

### 入庫管理
- 商品の入庫登録
- ロット番号の登録
- 使用期限の登録
- 入庫数量の登録
- 入庫先ロケーションの指定
- 同一ロットの複数棚への分割配置
- 入庫処理と在庫数量の連動

### 出庫管理
- 出庫対象商品の選択
- ロットおよび棚の選択
- 出庫数量の登録
- 在庫数を超える出庫の防止
- 出庫処理と在庫数量の連動

### 在庫管理
- 商品別在庫の確認
- ロット別在庫の確認
- 棚別在庫の確認
- 使用期限の確認
- 複数棚に分散した在庫の集計

### 入出庫履歴
- 入庫履歴の保存
- 出庫履歴の保存
- 操作日時の記録
- 対象商品・数量・処理種別の確認
- 在庫変動の追跡

### スタッフ管理
- スタッフ一覧表示
- スタッフ登録
- スタッフ情報の編集
- スタッフの有効・無効管理
- 社員番号の発行
- 仮PINの発行
- 登録完了メールの送信
- 管理者とスタッフの役割管理
  
## DB設計、ER図

<img width="1175" height="431" alt="Image" src="https://github.com/user-attachments/assets/be9e93b4-30ad-47ef-be55-45e333aaf515" />

<img width="953" height="396" alt="Image" src="https://github.com/user-attachments/assets/768e90f4-11ba-4277-8df5-51af9aec970d" />

<img width="1191" height="279" alt="Image" src="https://github.com/user-attachments/assets/251e3e62-bb60-408f-92b3-163a2605eb7d" />


<img width="2557" height="3754" alt="Image" src="https://github.com/user-attachments/assets/d360ce56-9939-4318-9dde-99c378cf6961" />


## 使用技術

### バックエンド

・Laravel 10.5

・PHP 8.2

・MySQL 8.0.26

・Fortfy（laravel認証）

・Api(Token方式)/Sanctum　

・MailHog

・FormRequest（laravelバリデーション）

### インフラ・開発環境
・Nginx 1.21.1

・Docker

・phpMyAdmin

・Git / GitHub

・Railway（Laravel API・MySQLの公開）

## 認証方式

- Laravel SanctumのBearerトークン認証を使用しています。
- ログイン成功時にアクセストークンを発行し、以後のAPIリクエストではAuthorizationヘッダーに付与します。
- APIリクエスト時は `Authorization: Bearer {token}` の形式で認証します。
- 管理者（admin）とスタッフ（staff）のロールに応じて、アクセス可能な画面・機能を制御しています。

## 環境構築手順
### 1 Gitファイルをクローンする
 git clone https://github.com/shoyama1010/drugs-stock-app.git

### 2 Dockerコンテナを作成する
 cd drugs-stock-app

 docker compose up -d --build

### 3 Laravelパッケージをインストールする

◆ /var/www で実行すると composer.json が存在しないため、Composerのインストールを実行できませんので、下記のとおりにお願いします。

 docker compose exec php bash(PHPコンテナにログインし)

 cd src　（cd /var/www/srcのようにする）

### 4　Laravelの依存パッケージをインストール

　composer install

### 5 .envファイルを作成する
 cp .env.example .env

 env のDB設定を以下のように確認する
 - DB_CONNECTION=mysql 
 - DB_HOST=mysql
 - DB_PORT=3306
 - DB_DATABASE=drugstore
 - DB_USERNAME=laravel
 - DB_PASSWORD=secret

※ docker-compose.yml の MySQL 設定と一致するようにしてください。

## 6 アプリケーションキーを生成

 php artisan key:generate   

## 7 テーブル及び初期データの作成

 php artisan migrate --seed

*最後に
- php artisan optimize:clear

### メール設定（MailHog）

スタッフ登録時に仮PINをメール送信するため、MailHogを使用しています。  
env のメール設定は以下にしてください。

- MAIL_MAILER=smtp
- MAIL_HOST=mailhog
- MAIL_PORT=1025
- MAIL_USERNAME=null
- MAIL_PASSWORD=null
- MAIL_ENCRYPTION=null
- MAIL_FROM_ADDRESS=hello@example.com
- MAIL_FROM_NAME="${APP_NAME}"

## 8 テスト

本アプリでは主要機能について Feature Test を実装し、認証・権限制御・入出庫処理・バリデーション・DB更新・履歴保存を検証しています。

### 実装済テスト対象

#### 認証機能
- 管理者ログイン
- スタッフログイン

#### スタッフ管理
- スタッフユーザーはスタッフ管理画面へアクセスできない
- スタッフユーザーはスタッフ登録を実行できない

#### 入庫機能
- 未認証ユーザーは入庫できない
- 入庫処理が正常に完了する
- 入庫数量がDBへ正しく保存される
- 入庫履歴が `transactions` テーブルへ保存される
- 入庫数量が0の場合はバリデーションエラーになる

#### 出庫機能
- 未認証ユーザーは出庫できない
- 出庫処理が正常に完了する
- 出庫後の在庫数量が正しく減少する
- 出庫履歴が `transactions` テーブルへ保存される
- 在庫数を超える出庫は拒否される
- 在庫超過で出庫に失敗した場合、在庫数が変更されない
- 出庫数量が0の場合はバリデーションエラーになる

以下の内容を確認
- 認証済ユーザーによる操作が可能であること
- 入出庫処理が正常に完了すること
- transactions テーブルに履歴が記録されること

### テスト実行方法

テスト用DBとして `drugstore_test` を使用します。

- テスト用DB作成（.env.testing）
- tests/Feature/配下に、各テスト用ファイル作成
- テスト用mysqlのため、マイグレーションを実行

- 各ファイルごとに、php artisan test

テストは `.env.testing` を用いて、本番DBと分離した環境で実行

```env
APP_ENV=testing

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=drugstore_test
DB_USERNAME=laravel
DB_PASSWORD=secret

## 9 工夫した点

### スタッフログイン・初回PIN変更機能

スタッフは管理者用アカウントとは分離し、社員番号と4桁のPINを使用してログインできるようにしています。

管理者がスタッフを新規登録すると、社員番号と仮PINを自動生成します。

ローカル環境ではMailHogによるメール通知を行い、あわせて登録完了モーダルにも社員番号・仮PINを表示します。

初回ログイン時は、`users` テーブルの `is_pin_changed` を確認し、未変更の場合は初回PIN変更画面へ遷移させます。

PIN変更完了後は `is_pin_changed = 1` に更新し、2回目以降のログインでは初回PIN変更画面を表示せず、
スタッフ用ダッシュボードへ直接遷移するようにしました。

#### スタッフログインの流れ

1. 管理者がスタッフを登録
2. 社員番号・仮PINを自動生成
3. スタッフが社員番号・仮PINで初回ログイン
4. `is_pin_changed = 0` の場合、初回PIN変更画面へ遷移
5. 新しいPINへ変更
6. `is_pin_changed = 1` に更新
7. 次回以降は変更後PINでスタッフ用ダッシュボードへログイン

PINは平文では保存せず、ハッシュ化した状態で `pin_hash` に保存しています。

入庫・出庫処理と在庫数を連動させ、操作履歴をtransactionsテーブルに記録する構成を実装しています。

Laravel APIとReactを分離し、Sanctumを利用した認証付きSPAとして構成しています。

## 10 苦労した点

１．PIN初期画面の改良

- スタッフの仮PIN発行・ハッシュ化・初回PIN変更までの認証処理を正しく連携させるのに苦労しました。

２．入庫・出庫処理による在庫数の変更と履歴データの整合性を保つ処理の実装に時間が掛かりました。

３．Laravel APIとReact間で発生する認証エラーやバリデーションエラーを確認し、原因を切り分けながら調整するのが苦労しました。

４． Laravel側の認証状態とReact側の画面遷移を連携させ、管理者・スタッフそれぞれの認証フローを整える点に時間が掛かりました。

## 将来への改善
### スタッフでの入出庫改善

- 開発順序

①スタッフダッシュボードに「入庫」「出庫」ボタン追加

②スタッフから既存の入庫・出庫画面へ遷移

③transactions.user_id にログインスタッフIDが入ることを確認

④管理者の履歴画面に「担当者名」を表示

⑤スタッフ側に「自分の作業履歴」を追加

⑥最後にAPI側のロール権限を整理

### 配送機能の開発予定

- 開発順序(テーブル設計調整あり)

- 配送先店舗選択・・・storeテーブル
- 商品選択
- 数量入力
- 出荷伝票発行・・・shipments / shipment_itemsの各テーブル
- 出荷確定⇒在庫数量状況・・・transactions / stock_lot_locationsの各テーブル

開発視点・・・将来的にネット受注（注文）ありの開発を目的としてますが、現状としては確実に「配送完了」の視点で開発します。

shipments

→ 出荷伝票の親データ

→ 店舗、状態、出荷日

shipment_items

→ 出荷伝票の明細

→ 商品、数量

transactions
→ 実際に在庫を動かした履歴





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
