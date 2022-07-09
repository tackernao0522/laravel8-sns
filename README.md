# Laravelの時刻設定を日本時間にする

+ `server/config/app.php`を編集<br>

```php:app.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Tokyo', // 編集

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Js' => Illuminate\Support\Js::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

];
```

# 1-2 ルーティングの追加

+ `$ php artisan make:controller ArticleController`を実行<br>

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController; // 追加
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ArticleController::class, 'index']); // 編集
```

# 2. コントローラーの編集

+ `server/app/Http/Controllers/ArticleController.php`を編集<br>

```php:ArticleController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        // ダミーデータ
        $articles = [
            (object) [
                'id' => 1,
                'title' => 'タイトル1',
                'body' => '本文1',
                'created_at' => now(),
                'user' => (object)[
                    'id' => 1,
                    'name' => 'ユーザー名1',
                ],
            ],
            (object) [
                'id' => 2,
                'title' => 'タイトル2',
                'body' => '本文2',
                'created_at' => now(),
                'user' => (object)[
                    'id' => 2,
                    'name' => 'ユーザー名2',
                ],
            ],
            (object) [
                'id' => 3,
                'title' => 'タイトル3',
                'body' => '本文3',
                'created_at' => now(),
                'user' => (object)[
                    'id' => 3,
                    'name' => 'ユーザー名3',
                ],
            ],
        ];

        return view('articles.index', compact('articles'));
    }
}
```

# 1-4 記事一覧画面とナビバーの作成

+ `$ touch resources/views/app.blade.php`を実行<br>

+ `resources/views/app.blade.php`を編集<br>

```html:app.blade.php
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>
        @yield('title')
    </title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.11/css/mdb.min.css" rel="stylesheet">
</head>

<body>

    @yield('content')

    <!-- JQuery -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Bootstrap tooltips -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js">
    </script>
    <!-- MDB core JavaScript -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.11/js/mdb.min.js"></script>
</body>

</html>
```

+ `$ mkdir resources/views/articles && touch $_/index.blade.php`を実行<br>

+ `resources/views/articles/index.blade.php`を編集<br>

```html:index.blade.php
@extends('app')

@section('title', '記事一覧')

@section('content')
    <div class="container">
        <div class="card mt-3">
            <div class="card-body d-flex flex-row">
                <i class="fas fa-user-circle fa-3x mr-1"></i>
                <div>
                    <div class="font-weight-bold">
                        ユーザー名
                    </div>
                    <div class="font-weight-lighter">
                        2020/2/1 12:00
                    </div>
                </div>
            </div>
            <div class="card-body pt-0 pb-2">
                <h3 class="h4 card-title">
                    記事タイトル
                </h3>
                <div class="card-text">
                    記事本文
                </div>
            </div>
        </div>
    </div>
@endsection
```

## 記事一覧へのダミーデータの表示

+ `resources/views/articles/index.blade.php`を編集<br>

```html:index.blade.php
@extends('app')

@section('title', '記事一覧')

@section('content')
    <div class="container">
        @foreach ($articles as $article) <!-- 追加 -->
            <div class="card mt-3">
                <div class="card-body d-flex flex-row">
                    <i class="fas fa-user-circle fa-3x mr-1"></i>
                    <div>
                        <div class="font-weight-bold">
                            {{ $article->user->name }} <!-- 編集 -->
                        </div>
                        <div class="font-weight-lighter">
                            {{ $article->created_at->format('Y/m/d H:i') }} <!-- 編集 -->
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 pb-2">
                    <h3 class="h4 card-title">
                        {{ $article->title }} <!-- 編集 -->
                    </h3>
                    <div class="card-text">
                        {!! nl2br(e($article->body)) !!} <!-- 編集 -->
                    </div>
                </div>
            </div>
        @endforeach <!-- 追加 -->
    </div>
@endsection
```

+ `$ touch resources/views/nav.blade.php`を実行<br>

+ `resources/views/nav.blade.php`を編集<br>

```html:nav.blade.php
<nav class="navbar navbar-expand navbar-dark blue-gradient">

    <a href="/" class="navbar-brand"><i class="far fa-sticky-not mr-1"></i>memo</a>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item">
            <a href="" class="nav-link">ユーザー登録</a>
        </li>

        <li class="nav-item">
            <a href="" class="nav-link">ログイン</a>
        </li>
        <li class="nav-item">
            <a href="" class="nav-link"><i class="fas fa-pen mr-1"></i>投稿する</a>
        </li>

        {{-- Dropdown --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fas fa-user-circle"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-primary" aria-labelledby="navbarDropdownMenuLink">
                <button class="dropdown-item type="button" onclick="location.href="">
                    マイページ
                </button>
                <div class="dropdown-divider"></div>
                <button form="logout-button" class="dropdown-item" type="submit">
                    ログアウト
                </button>
            </div>
        </li>
        <form action="" id="logout-button" method="POST">
        </form>
        {{-- Dropdown --}}
    </ul>
</nav>
```

+ `resources/views/articles/index.blade.php`を編集<br>

```html:index.blade.php
@extends('app')

@section('title', '記事一覧')

@section('content')
    @include('nav') {{-- 追加 --}}
    <div class="container">
        @foreach ($articles as $article)
            <div class="card mt-3">
                <div class="card-body d-flex flex-row">
                    <i class="fas fa-user-circle fa-3x mr-1"></i>
                    <div>
                        <div class="font-weight-bold">
                            {{ $article->user->name }}
                        </div>
                        <div class="font-weight-lighter">
                            {{ $article->created_at->format('Y/m/d H:i') }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 pb-2">
                    <h3 class="h4 card-title">
                        {{ $article->title }}
                    </h3>
                    <div class="card-text">
                        {!! nl2br(e($article->body)) !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
```