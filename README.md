# 環境構築

## メール認証

MailHog を使用しています。<br>

## テーブル仕様

### users テーブル

## ER 図

## テストアカウント

管理者<br>
メールアドレス:admin@example
パスワード:password12345

---

一般ユーザー<br>
email:
password: pass12345

## PHPUnit を利用したテストに関して

```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;
```

config ディレクトリの中の database.php を開き、mysql の配列部分をコピーして以下に新たに mysql_test を作成
以下の項目を編集

- 'database' => env('DB_DATABASE', 'forge')
- 'username' => env('DB_USERNAME', 'forge')
- 'password' => env('DB_PASSWORD', '')

* 'database' => 'test_database',
* 'username' => 'root',
* 'password' => 'root',

テスト用の.env ファイル作成

```
$ cp .env .env.testing
```

.env.testing ファイルの APP_ENV と APP_KEY を編集
APP_NAME=Laravel

- APP_ENV=local
- APP_KEY=base64:vPtYQu63T1fmcyeBgEPd0fJjvmnzjYMaUf7d5iuB+c=

* APP_ENV=test
* APP_KEY=
  APP_DEBUG=true
  APP_URL=http://localhost

DB_DATABASE,DB_USERNAME,DB_PASSWORD を編集

- DB_CONNECTION=mysql

* DB_CONNECTION=mysql_test
  DB_HOST=mysql
  DB_PORT=3306

- DB_DATABASE=laravel_db
- DB_USERNAME=laravel_user
- DB_PASSWORD=laravel_pass

* DB_DATABASE=test_database
* DB_USERNAME=root
* DB_PASSWORD=root

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
-     <!-- <server name="DB_CONNECTION" value="sqlite"/> -->
-     <!-- <server name="DB_DATABASE" value=":memory:"/> -->
+     <server name="DB_CONNECTION" value="mysql_test"/>
+     <server name="DB_DATABASE" value="test_database"/>
    <server name="MAIL_MAILER" value="array"/>
    <server name="QUEUE_CONNECTION" value="sync"/>
    <server name="SESSION_DRIVER" value="array"/>
    <server name="TELESCOPE_ENABLED" value="false"/>
</php>

```
php artisan test
```
