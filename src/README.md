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

### 6 アプリケーションキーを生成

 php artisan key:generate   

### 7 テーブル及び初期データの作成

 php artisan migrate --seed

*最後に
php artisan optimize:clear

## メール設定（MailHog）

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

## テスト

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

テストは .env.testing` を用いて、本番DBと分離した環境で実行

APP_ENV=testing

DB_CONNECTION=mysql

DB_HOST=mysql

DB_PORT=3306

DB_DATABASE=drugstore_test

DB_USERNAME=laravel

DB_PASSWORD=secret

#### テスト用DBへマイグレーションを実行

php artisan migrate --env=testing

#### 全テストを実行

php artisan test --env=testing

#### テスト結果

Tests: 17 passed、Assertions: 38

## 工夫した点

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

### 入庫・出庫処理と在庫管理

入庫・出庫処理と在庫数を連動させ、操作履歴を `transactions` テーブルに記録する構成を実装しました。

### Laravel APIとReactの分離

Laravel APIとReactを分離し、Sanctumを利用した認証付きSPAとして構成しています。

## 苦労した点

### 1. スタッフ認証フローの構築

**課題**  
仮PIN発行・ハッシュ化・初回PIN変更までの認証状態を
正しく連携させる必要がありました。

**対応**  
`is_pin_changed` によって初回ログインかどうかを判定し、
PIN変更後はスタッフ用ダッシュボードへ遷移するよう認証フローを整理しました。

### 2. 入庫・出庫と在庫数の整合性

**課題**  
入庫・出庫処理と在庫数、履歴データの整合性を保つ必要がありました。

**対応**  
在庫更新と同時に `transactions` テーブルへ操作履歴を記録し、
在庫変動を追跡できる構成にしました。

### 3. Laravel APIとReact間のエラー対応

**課題**  
認証エラーやバリデーションエラーの原因を
Laravel側・React側のどちらにあるか切り分ける必要がありました。

**対応**  
APIレスポンスとブラウザ側の通信内容を確認しながら、
認証・バリデーション処理を段階的に調整しました。

## 今後の改善予定

### 使用期限の改善

現在、使用期限はロット単位で登録・保持しています。今後は使用期限の近いロットから優先して出庫するFEFOや、期限切迫在庫のアラート機能への活用を予定しています。

### スタッフによる入出庫機能の改善

現在は管理者は勿論、スタッフ用ダッシュボードからも入庫・出庫処理を行えますが、更に「在庫画面上の操作」にて、「スタッフ用ログイン」によって、入出庫処理が行えるよう拡張する予定です。

#### 実装予定

- スタッフダッシュボードに「入庫」「出庫」ボタンを追加
- 既存の入庫・出庫画面への遷移
- `transactions.user_id` にログインスタッフIDを保存
- 管理者の履歴画面に担当者名を表示
- スタッフ側に「自分の作業履歴」を追加
- API側のロール・権限を整理

### 配送機能の追加

出庫処理から配送先店舗への出荷までを管理できるよう、
配送機能の追加を予定しています。

#### 実装予定

- 配送先店舗の選択
- 商品・数量の指定
- 出荷伝票の発行
- 出荷確定による在庫数の更新
- 配送ステータスの管理

#### 使用予定テーブル

| テーブル | 役割 |
|---|---|
| `shipments` | 出荷伝票の基本情報 |
| `shipment_items` | 出荷商品・数量 |
| `transactions` | 実際の在庫変動履歴 |

`shipments` では `draft / confirmed / shipped` などの
ステータスを管理する構成を検討しています。


