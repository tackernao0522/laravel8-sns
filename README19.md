# 3-2 CircleCIでテストを実行する

+ [circleciでLaravelのプロジェクトのCIが落ちるようになった - Qiita](https://qiita.com/tabtt3/items/996e512e7002e9f26b0a) <br>

+ [run - Circle公式ドキュメント](https://circleci.com/docs/ja/2.0/configuration-reference/#run) <br>

+ `root $ .circelci/config.yml`を編集<br>

```yml:config.yml
version: 2.1
jobs:
  build:
    docker:
      - image: circleci/php:8.0-node-browsers-legacy
    steps:
      - checkout
      - run: sudo composer self-update --2
      - restore_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
      - run: composer install -n --prefer-dist --working-dir=./server/
      - save_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
          paths:
            - .server/vendor
      - run:
          name: npm ci
          command: cd server/; npm ci
      - run: cd server/; npm run dev
      # - run: cd server/; composer dump-autoload
      - run:
          name: php test
          command: cd server/; vendor/bin/phpunit
```

+ `$ cp .env .env.testing`を実行<br>

+ `server/.env.testing`を編集<br>

```:.env.testing
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:t0Pz8LBbbLtBBcr0GviodmnndawvfUkPi6CvQ+lO6u4=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# コメントアウトする
# DB_CONNECTION=mysql
# DB_HOST=laravel8snsdb-host
# DB_PORT=3306
# DB_DATABASE=laravl8sns-database
# DB_USERNAME=laravel8_sns
# DB_PASSWORD=5t5a7k3a

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# MAIL_MAILER=smtp
# MAIL_HOST=mailhog
# MAIL_PORT=1025
# MAIL_USERNAME=null
# MAIL_PASSWORD=null
# MAIL_ENCRYPTION=null
# MAIL_FROM_ADDRESS=null
# MAIL_FROM_NAME="${APP_NAME}"

#Sendgrid用
MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=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
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME=memo
MAIL_FROM_ADDRESS=takaki_5573031@yahoo.co.jp

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

GOOGLE_CLIENT_ID=968776725793-9b4qhbjuvhgtifi1410310biqfrmv80j.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

+ `$ php artisan key:generate --show`を実行<br>

+ `server/.env.testing`を編集<br>

```:.env.testing
APP_NAME=Laravel
APP_ENV=local
# 編集
APP_KEY=base64:t0Pz8LBbbLtBBcr0GviodmnndawvfUkPi6CvQ+lO6u4=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# DB_CONNECTION=mysql
# DB_HOST=laravel8snsdb-host
# DB_PORT=3306
# DB_DATABASE=laravl8sns-database
# DB_USERNAME=laravel8_sns
# DB_PASSWORD=5t5a7k3a

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# MAIL_MAILER=smtp
# MAIL_HOST=mailhog
# MAIL_PORT=1025
# MAIL_USERNAME=null
# MAIL_PASSWORD=null
# MAIL_ENCRYPTION=null
# MAIL_FROM_ADDRESS=null
# MAIL_FROM_NAME="${APP_NAME}"

#Sendgrid用
MAIL_DRIVER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=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
MAIL_ENCRYPTION=tls
MAIL_FROM_NAME=memo
MAIL_FROM_ADDRESS=takaki_5573031@yahoo.co.jp

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

GOOGLE_CLIENT_ID=968776725793-9b4qhbjuvhgtifi1410310biqfrmv80j.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

+ `server/phpunit.xml`を編集<br>

```xml:phpunit.xml
<?xml version="1.0" encoding="UTF-8" ?>
<phpunit
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
  bootstrap="vendor/autoload.php"
  colors="true"
>
    <testsuites>
        <!-- <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite> -->
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
    <php>
        <server name="APP_ENV" value="testing" />
        <server name="BCRYPT_ROUNDS" value="4" />
        <server name="CACHE_DRIVER" value="array" />
        <server name="DB_CONNECTION" value="sqlite"/>
        <server name="DB_DATABASE" value=":memory:"/>
        <server name="MAIL_MAILER" value="array" />
        <server name="QUEUE_CONNECTION" value="sync" />
        <server name="SESSION_DRIVER" value="array" />
        <server name="TELESCOPE_ENABLED" value="false" />
    </php>
</phpunit>
```

## 6. CircleCIでテストを実行する

+ githubにpushしてCircleCIでテストが通ればOK <br>

# 3-4 キャッシュを利用する(npm)

+ `.circleci/config.yml`を編集<br>

```yml:config.yml
version: 2.1
jobs:
  build:
    docker:
      - image: circleci/php:8.0-node-browsers-legacy
    steps:
      - checkout
      - run: sudo composer self-update --2
      - restore_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
      - run: composer install -n --prefer-dist --working-dir=./server/
      - save_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
          paths:
            - .server/vendor
      - restore_cache:
          key: npm-v1-{{ checksum "./server/package-lock.json" }}
      - run:
          name: npm ci
          command: |
            if [ ! -d ./server/node_modules ]; then
              cd server/; npm ci
            fi
      - save_cache:
          key: npm-v1-{{ checksum "./server/package-lock.json" }}
          paths:
            - ./server/node_modules
      - run: cd server/; npm run dev
      - run:
          name: php test
          command: cd server/; vendor/bin/phpunit
```

+ [if文とtestコマンド](https://shellscript.sunone.me/if_and_test.html#if-%E6%96%87%E3%81%A8-test-%E3%82%B3%E3%83%9E%E3%83%B3%E3%83%89) <br>

+ [npm-ci | npm Documentation](https://docs.npmjs.com/cli/ci.html#example) <br>

## 2. CircleCIを実行する<br>

+ githubにpushしてCircleCIが問題なければOK <br>

# 3-6 CircleCIでMySQLを使用する

+ `.circleci/config.yml`を編集<br>

```yml:config.yml
version: 2.1
jobs:
  build:
    docker:
      - image: circleci/php:8.0-node-browsers-legacy
      - image: cimg/mysql:8.0
    environment:
      - APP_ENV: testing
      - DB_CONNECTION: circle_test
      - MYSQL_ALLOW_EMPTY_PASSWORD: true;
    steps:
      - checkout
      - run: sudo composer self-update --2
      - restore_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
      - run: composer install -n --prefer-dist --working-dir=./server/
      - save_cache:
          key: composer-v1-{{ checksum "./server/composer.lock" }}
          paths:
            - .server/vendor
      - restore_cache:
          key: npm-v1-{{ checksum "./server/package-lock.json" }}
      - run:
          name: npm ci
          command: |
            if [ ! -d ./server/node_modules ]; then
              cd server/; npm ci
            fi
      - save_cache:
          key: npm-v1-{{ checksum "./server/package-lock.json" }}
          paths:
            - ./server/node_modules
      - run: cd server/; npm run dev
      - run:
          name: get ready for mysql
          command: |
            sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4EB27DB2A3B88B8B
            sudo apt-get update
            sudo apt-get install default-mysql-client
            sudo docker-php-ext-install pdo_mysql
            dockerize -wait tcp://localhost:3306 -timeout 1m
      - run:
          name: php test
          command: cd server/; vendor/bin/phpunit
```

+ `server/config/database.php`を編集<br>

```php:database.php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'circle_test' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'circle_test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
```

## 2. CircleCIを実行する

+ githubにpushして通ればOK <br>

+ [参考](https://zenn.dev/gomo/articles/7f6c28d002837c) <br>
