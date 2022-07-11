# 2-8 SQLやtinkerを使ったデータ操作

mysql 接続参考: https://techtechmedia.com/docker-mysql-connection/ <br>

# 3-3 ログイン画面の作成

+ `server/resources/views/auth/login.blade.php`を編集<br>

```php:login.blade.php
@extends('app')

@section('title', 'ログイン')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">ログイン</h2>

                        @include('error_card_list')

                        <div class="card-text">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" id="email" name="email" class="form-control"
                                        value="{{ old('email') }}" required>
                                </div>

                                <div class="md-form">
                                    <label for="password">パスワード</label>
                                    <input class="form-control" type="password" id="password" name="password" required>
                                </div>

                                <input type="hidden" name="remember" id="remember" value="on">

                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">ログイン</button>
                            </form>

                            <div class="mt-0">
                                <a href="{{ route('register') }}" class="card-text">ユーザー登録はこちら</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

+ `server/resources/views/nav.blade.php`を編集<br>

```php:nav.blade.php
<nav class="navbar navbar-expand navbar-dark blue-gradient">

    <a href="/" class="navbar-brand"><i class="far fa-sticky-not mr-1"></i>memo</a>

    <ul class="navbar-nav ml-auto">

        @guest
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">ユーザー登録</a>
            </li>
        @endguest

        @guest
            <li class="nav-item">
                // 編集
                <a href="{{ route('login') }}" class="nav-link">ログイン</a>
            </li>
        @endguest

        @auth
            <li class="nav-item">
                <a href="" class="nav-link"><i class="fas fa-pen mr-1"></i>投稿する</a>
            </li>
        @endauth

        @auth
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
            <form action="{{ route('logout') }}" id="logout-button" method="POST">
                @csrf
            </form>
            {{-- Dropdown --}}
        @endauth
    </ul>
</nav>
```

## 5. 認証エラーメッセージの日本語化

+ `$ touch resources/lang/ja/auth.php`を実行<br>

+ `resources/lang/ja/auth.php`を編集<br>

```php:auth.php
<?php

return [

  /*
    |--------------------------------------------------------------------------
    | 認証言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は認証時にユーザーに対し表示する必要のある
    | 様々なメッセージです。アプリケーションの必要に合わせ
    | 自由にこれらの言語行を変更してください。
    |
    */

  'failed' => 'ログイン情報が登録されていません。',
  'throttle' => 'ログインに続けて失敗しています。:seconds秒後に再度お試しください。',

];
```

# 4-2 ルーティングの追加

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', [ArticleController::class, 'index'])->name('articles.index'); // 編集
Route::resource('articles', ArticleController::class)->except(['index']); // 追加
```

# 4-3 記事投稿画面の作成と未ログイン時の考慮

+ `src/app/Http/Controllers/ArticleController.php`を編集<br>

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

    public function create() // 追加
    {
        return view('articles.create');
    }
}
```

+ `$ touch resources/views/articles/create.blade.php`を実行<br>

+ `resources/views/articles/create.blade.php`を編集<br>

```html:create.blade.php
@extends('app')

@section('title', '記事投稿')

@include('nav')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-body pt-0">
                        @include('error_card_list')
                        <div class="card-text">
                            <form method="POST" action="{{ route('articles.store') }}">
                                @include('articles.form')
                                <button class="btn blue-gradient btn-block">
                                    投稿する
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

+ `touch resources/views/articles/form.blade.php`を実行<br>

+ `resources/views/articles/form.blade.php`を編集<br>

```html:form.blade.php
@csrf
<div class="md-form">
    <label>タイトル</label>
    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
</div>
<div class="form-group">
    <label></label>
    <textarea name="body" class="form-control" rows="16" placeholder="本文" required>{{ old('body') }}</textarea>
</div>
```

+ `resources/views/nav.blade.php`を編集<br>

```html:nav.blade.php
<nav class="navbar navbar-expand navbar-dark blue-gradient">

    <a href="/" class="navbar-brand"><i class="far fa-sticky-not mr-1"></i>memo</a>

    <ul class="navbar-nav ml-auto">

        @guest
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">ユーザー登録</a>
            </li>
        @endguest

        @guest
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">ログイン</a>
            </li>
        @endguest

        @auth
            <li class="nav-item">
                {{-- 編集 --}}
                <a href="{{ route('articles.create') }}" class="nav-link"><i class="fas fa-pen mr-1"></i>投稿する</a>
            </li>
        @endauth

        @auth
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
            <form action="{{ route('logout') }}" id="logout-button" method="POST">
                @csrf
            </form>
            {{-- Dropdown --}}
        @endauth
    </ul>
</nav>
```

## 5. 未ログイン時の考慮

+ `routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
Route::resource('articles', ArticleController::class)->except(['index'])->middleware('auth'); // 編集
```

# 4-4 フォームリクエストの作成

+ `$ php artisan make:request ArticleRequest`を実行<br>

+ `src/app/Http/Request/ArticleRequest.php`を編集<br>

```php:ArticleRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:50',
            'body' => 'required|max:500',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
        ];
    }
}
```
