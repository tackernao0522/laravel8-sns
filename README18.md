# 2-4 記事一覧画面表示機能のテスト

## 記事一覧画面表示機能テスト

## 1. テストの作成

+ `$ php artisan make:test ArticleControllerTest`を実行<br>

## 2. テストの編集

+ `server/tests/Feature/ArticleControllerTest.php`を編集<br>

```php:ArticleControllerTest.php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200)
            ->assertViewIs('articles.index');
    }
}
```

+ [トレイト - PHP公式マニュアル](https://www.php.net/manual/ja/language.oop5.traits.php) <br>

+ [@test - PHPUnit公式](https://phpunit.readthedocs.io/ja/latest/annotations.html#test) <br>

+ [HTTPテスト - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html) <br>

+ [名前付きルートへのURLを生成する - Laravel公式](https://readouble.com/laravel/6.x/ja/routing.html#named-routes) <br>

+ [200 OK - MDN](https://developer.mozilla.org/ja/docs/Web/HTTP/Status/200) <br>

+ [assertStatus - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html#assert-status) <br>

+ [assertOK - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html#assert-ok) <br>

+ [assertViewIs - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html#assert-view-is) <br>

## 3. テストの実行

+ `$ vendor/bin/phpunit`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:02.680, Memory: 26.00 MB

OK (1 test, 2 assertions)
```
