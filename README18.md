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

# 2-6 ログイン前後での記事投稿画面表示のテスト

## ログイン前後での記事投稿画面表示のテスト

## 1. テストの編集（未ログイン状態のケース）


+ `server/test/Feature/ArticleControllerTest.php`を編集<br>

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

    // 追加
    public function testGuestCreate()
    {
        $response = $this->get(route('articles.create'));

        $response->assertRedirect(route('login'));
    }
}
```

+ [assertRedirect - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html#assert-redirect) <br>

## 2. テストの実行(未ログイン状態のケース)

+ `$ vendor/bin/phpunit --filter=guest`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:01.530, Memory: 26.00 MB

OK (1 test, 2 assertions)
```

+ [--filter - PHPUnit公式](https://phpunit.readthedocs.io/ja/latest/textui.html?highlight=--filter) <br>

## 3. テストの編集(ログイン済み状態のケース)

+ `server/tests/Feature/ArticleControllerTest.php`を編集<br>

```php:ArticleControllerTest.php
<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function testGuestCreate()
    {
        $response = $this->get(route('articles.create'));

        $response->assertRedirect(route('login'));
    }

    // 追加
    public function testAuthCreate()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('articles.create'));

        $response->assertStatus(200)
            ->assertViewIs('articles.create');
    }
}
```

+ [モデルの保存 - Laravel公式](https://readouble.com/laravel/6.x/ja/database-testing.html#persisting-models) <br>

+ [セッション/認証 - Laravel公式](https://readouble.com/laravel/6.x/ja/http-tests.html#session-and-authentication) <br>

## 4. テストの実行(ログイン済み状態のケース)

+ `$ vendor/bin/phpunit --filter=auth`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:02.922, Memory: 28.00 MB

OK (1 test, 2 assertions)
```

+ [Arrange-Act-Assert](http://wiki.c2.com/?ArrangeActAssert) <br>

# 2-7 いいねされているかを判定するメソッドの把握

+ [関数の引数 - 型宣言 - PHP公式マニュアル](https://www.php.net/manual/ja/functions.arguments.php#functions.arguments.type-declaration) <br>

+ [nullableな型 - PHP公式マニュアル](https://www.php.net/manual/ja/migration71.new-features.php#migration71.new-features.nullable-types) <br>

+ [戻り値の型宣言 - PHP公式マニュアル](https://www.php.net/manual/ja/migration70.new-features.php#migration70.new-features.return-type-declarations) <br>

+ [三項演算子 - PHP公式マニュアル](https://www.php.net/manual/ja/language.operators.comparison.php#language.operators.comparison.ternary) <br>

#### ■ 動的プロパティ

+ [多対多 - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html#many-to-many) <br>

#### likes テーブル

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|いいねを識別するID|
|user_id|整数|いいねしたユーザーのid|
|article_id|整数|いいねされた記事のid|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

+ [コレクション - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html) <br>

#### ■ whereメソッド

+ [where - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-where) <br>

+ [count - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-count) <br>

+ [型キャスト - PHP公式マニュアル](https://www.php.net/manual/ja/language.types.type-juggling.php#language.types.typecasting) <br>

# 2-8 いいねされているかを判定するメソッドのテストとファクトリの作成

## 1. テストの作成

+ `$ php artisan make:test ArticleTest`を実行`<br>

## 2. テストの編集(引数がnullのケース)

+ `server/tests/Feature/ArticleTest.php`を編集<br>

```php:ArticleTest.php
<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function testIsLikedByNull()
    {
        $article = Article::factory()->create();

        $result = $article->isLikedBy(null);

        $this->assertFalse($result);
    }
}
```

+ [assertFalse - PHPUnit公式](https://phpunit.readthedocs.io/ja/latest/assertions.html#assertfalse) <br>

## 3. ファクトリの作成

+ `$ php artisan make:factory ArticleFactory --model=Article`を実行<br>

+ `server/database/factories/ArticleFactory.php`を編集<br>

```php:ArticleFactory.php
<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->text(50),
            'body' => $this->faker->text(500),
            'user_id' => function () {
                return User::factory();
            }
        ];
    }
}
```

#### articles テーブル

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|記事を識別するID|
|title|最大255文字の文字列|記事のタイトル|
|body|制限なしの文字列|記事の本文|
|user_id|整数|記事の本文|
|user_id|整数|記事を投稿したユーザーID|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

+ `$ vendor/bin/phpunit --filter=null`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:01.537, Memory: 28.00 MB

OK (1 test, 1 assertion)
```

+ [fzaninotto/Faker - GitHub](https://github.com/fzaninotto/Faker) <br>

# 2-9 いいねされているかを判定するメソッドのテストの続き

## 1. テストの編集（いいねをしているケース)

+ `server/tests/Feature/ArticleTest.php`を編集<br>

```php:ArticleTest.php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function testIsLikedByNull()
    {
        $article = Article::factory()->create();

        $result = $article->isLikedBy(null);

        $this->assertFalse($result);
    }

    // 追加
    public function testIsLikedByTheUser()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $article->likes()->attach($user);

        $result = $article->isLikedBy($user);

        $this->assertTrue($result);
    }
}
```

+ [多対多 - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html#many-to-many) <br>

#### likes テーブル

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|いいねを識別するID|
|user_id|整数|いいねしたユーザーのid|
|article_id|整数|いいねされた記事のid|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

+ [attach/detach - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html#updating-many-to-many-relationships) <br>

+ [assertTrue - PHPUnit公式](https://phpunit.readthedocs.io/ja/latest/assertions.html#assertfalse) <br>

## 2. テストの実行(いいねをしているケース)

+ `$ vendor/bin/phpunit --filter=theuser`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:03.120, Memory: 28.00 MB

OK (1 test, 1 assertion)
```

## 3. テストの変数(いいねしていないケース)

+ `server/tests/Feature/ArticleTest.php`を編集<br>

```php:ArticleTest.php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function testIsLikedByNull()
    {
        $article = Article::factory()->create();

        $result = $article->isLikedBy(null);

        $this->assertFalse($result);
    }

    public function testIsLikedByTheUser()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $article->likes()->attach($user);

        $result = $article->isLikedBy($user);

        $this->assertTrue($result);
    }

    // 追加
    public function testIsLikedByAnother()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        $another = User::factory()->create();
        $article->likes()->attach($another);

        $result = $article->isLikedBy($user);

        $this->assertFalse($result);
    }
}
```

## 4. テストの実行(いいねをしていないケース)

+ `$ vendor/bin/phpunit --filter=another`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.                                                                   1 / 1 (100%)

Time: 00:02.053, Memory: 28.00 MB

OK (1 test, 1 assertion)
```

## 5. 全てのテストの実行

+ `$ vendor/bin/phpunit`を実行<br>

```
PHPUnit 9.5.21 #StandWithUkraine

.......                                                             7 / 7 (100%)

Time: 00:03.421, Memory: 32.00 MB

OK (7 tests, 10 assertions)
```
