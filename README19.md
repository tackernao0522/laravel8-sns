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

