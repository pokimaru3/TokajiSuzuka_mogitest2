# 環境構築

## メール認証

MailHog を使用しています。<br>

## テーブル仕様

### users テーブル

| カラム名          | 型                   | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| ----------------- | -------------------- | ----------- | ---------- | -------- | ----------- |
| id                | unsigned bigint      | ◯           |            | ◯        |             |
| name              | varchar(20)          |             |            | ◯        |             |
| email             | varchar(255)         |             | ◯          | ◯        |             |
| email_verified_at | timestamp            |             |            |          |             |
| password          | varchar(255)         |             |            | ◯        |             |
| role              | enum('user','admin') |             |            | ◯        |             |
| created_at        | timestamp            |             |            |          |             |
| updated_at        | timestamp            |             |            |          |             |

### attendances テーブル

| カラム名         | 型                                               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| ---------------- | ------------------------------------------------ | ----------- | ---------- | -------- | ----------- |
| id               | unsigned bigint                                  | ○           |            | ○        |             |
| user_id          | unsigned bigint                                  |             |            | ○        | users(id)   |
| work_date        | date                                             |             |            | ○        |             |
| work_status      | enum('off_duty','working','on_break','finished') |             |            | ○        |             |
| clock_in         | datetime                                         |             |            |          |             |
| clock_out        | datetime                                         |             |            |          |             |
| total_work_time  | integer                                          |             |            |          |             |
| total_break_time | integer                                          |             |            |          |             |
| remarks          | varchar(255)                                     |             |            | ○        |             |
| created_at       | timestamp                                        |             |            |          |             |
| updated_at       | timestamp                                        |             |            |          |             |

### break_times テーブル

| カラム名      | 型              | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY     |
| ------------- | --------------- | ----------- | ---------- | -------- | --------------- |
| id            | unsigned bigint | ○           |            | ○        |                 |
| attendance_id | unsigned bigint |             |            | ○        | attendances(id) |
| break_start   | datetime        |             |            | ○        |                 |
| break_end     | datetime        |             |            |          |                 |
| created_at    | timestamp       |             |            |          |                 |
| updated_at    | timestamp       |             |            |          |                 |

### attendance_correction_requests テーブル

| カラム名            | 型                         | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY     |
| ------------------- | -------------------------- | ----------- | ---------- | -------- | --------------- |
| id                  | unsigned bigint            | ○           |            | ○        |                 |
| attendance_id       | unsigned bigint            |             |            | ○        | attendances(id) |
| user_id             | unsigned bigint            |             |            | ○        | users(id)       |
| requested_clock_in  | datetime                   |             |            |          |                 |
| requested_clock_out | datetime                   |             |            |          |                 |
| remarks             | varchar(255)               |             |            | ○        |                 |
| status              | enum('pending','approved') |             |            | ○        |                 |
| created_at          | timestamp                  |             |            |          |                 |
| updated_at          | timestamp                  |             |            |          |                 |

### attendance_correction_breaks テーブル

| カラム名              | 型              | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY                        |
| --------------------- | --------------- | ----------- | ---------- | -------- | ---------------------------------- |
| id                    | unsigned bigint | ○           |            | ○        |                                    |
| correction_request_id | unsigned bigint |             |            | ○        | attendance_correction_requests(id) |
| requested_break_start | datetime        |             |            |          |                                    |
| requested_break_end   | datetime        |             |            |          |                                    |
| created_at            | timestamp       |             |            |          |                                    |
| updated_at            | timestamp       |             |            |          |                                    |

## ER 図

## テストアカウント

管理者<br>
メールアドレス:admin@example<br>
パスワード:password12345

---

一般ユーザー<br> 1.宮沢 京助
email: shuhei.nomura@example.com
password: pass12345<br> 2.青山 浩
email: snakamura@example.net
password: pass12345<br> 3.工藤 充
email: rika.nakamura@example.org
password: pass12345<br> 4.松本 太一
email: kenichi54@example.net
password: pass12345<br> 5.桐山 裕美子
email: satomi.uno@example.org
password: pass12345<br>

## PHPUnit のテストについて

```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;
```

config ディレクトリの中の database.php を開き、mysql の配列部分をコピーして以下に新たに mysql_test を作成
以下の項目を編集

- 'database' => env('DB_DATABASE', 'forge') -> 'database' => 'test_database'
- 'username' => env('DB_USERNAME', 'forge') -> 'username' => 'root'
- 'password' => env('DB_PASSWORD', '') -> 'password' => 'root'

テスト用の.env ファイル作成

```
$ cp .env .env.testing
```

.env.testing ファイルの APP_ENV と APP_KEY を編集

- APP_NAME=Laravel
- APP_ENV=local -> APP_ENV=test
- APP_KEY=base64:vPtYQu63T1fmcyeBgEPd0fJjvmnzjYMaUf7d5iuB+c= -> APP_KEY=
- APP_DEBUG=true
- APP_URL=http://localhost

DB_DATABASE,DB_USERNAME,DB_PASSWORD を編集

- DB_CONNECTION=mysql_test
- DB_HOST=mysql
- DB_PORT=3306
- DB_DATABASE=laravel_db -> DB_DATABASE=test_database
- DB_USERNAME=laravel_user -> DB_USERNAME=root
- DB_PASSWORD=laravel_pass -> DB_PASSWORD=root

アプリケーションキーを作成

```
$ php artisan key:generate --env=testing
```

マイグレーション

```
$ php artisan migrate --env=testing
```

phpunit.xml の編集

<php>
    <server name="APP_ENV" value="testing"/>
    <server name="BCRYPT_ROUNDS" value="4"/>
    <server name="CACHE_DRIVER" value="array"/>
-    <server name="DB_CONNECTION" value="mysql_test"/>
-    <server name="DB_DATABASE" value="test_database"/>
    <server name="MAIL_MAILER" value="array"/>
    <server name="QUEUE_CONNECTION" value="sync"/>
    <server name="SESSION_DRIVER" value="array"/>
    <server name="TELESCOPE_ENABLED" value="false"/>
</php>

```
php artisan test
```
