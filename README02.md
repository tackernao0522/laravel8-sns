# 1-5 記事テーブルとユーザーテーブルの作成

## 1. データベースの作成

## 3. 記事テーブルの作成

+ `php artisan make:migration create_articles_table --create=articles`を実行<br>

+ `server/database/migrations/create_articles_table.php`を編集<br>

```php:create_articles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
```

## 4. usersテーブルのマイグレションファイルの編集

+ `server/database/migrations/create_users_table.php`を編集<br>

```php:create_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 編集
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // 編集
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
```

## 5. マイグレーションの実行

+ `$ php artisan migrate`を実行<br>

# 1-6 記事モデルの作成

## 1. 記事モデルの作成

+ `$ php artisan make:model Article`を実行<br>

## 2. リレーションの追加

+ `server/app/Models/Article.php`を編集<br>

```php:Article.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo('App/Models/User');
    }
}
```

# 1-7 記事モデルから記事情報を取得する

## 1. コントローラの編集<br>

+ `server/app/Http/Controllers/ArticleController.php`を編集<br>

```php:ArticleController.php
<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all()->sortByDesc('created_at');

        return view('articles.index', compact('articles'));
    }
}
```

# 2-2 ルーティングの追加

## 1. 認証関連のルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(); // 追加
Route::get('/', [ArticleController::class, 'index']);
```
