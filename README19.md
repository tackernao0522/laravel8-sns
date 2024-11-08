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

+ `$ php artisan key:generate --show`を実行<br>

+ `server/.env.testing`を編集<br>

## 6. CircleCIでテストを実行する

+ githubにpushしてCircleCIでテストが通ればOK <br>
