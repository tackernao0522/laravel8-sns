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
      - run: sudo composer self-update --1
      - run: composer install -n --prefer-dist --working-dir=./server/
      - run:
          name: npm ci
          command: |
            if [ ! -d ./server/node_modules ]; then
              cd server/; npm ci
            fi
      - run: cd server/; npm run dev
      - run:
          name: php test
          command: php ./server/vendor/bin/phpunit --configuration=./server/phpunit.xml
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

## 6. CircleCIでテストを実行する

+ githubにpushしてCircleCIでテストが通ればOK <br>