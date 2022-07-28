# 9-12 Bladeの共通化

## 1. show.blade.phpの編集

+ `server/resources/views/users/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $user->name)

@section('content')
    @include('nav')
    <div class="container">
        @include('users/user')
        <ul class="nav nav-tabs nav-justified mt-3">
            <li class="nav-item">
                <a href="{{ route('users.show', $user->name) }}" class="nav-link text-muted active">
                    記事
                </a>
            </li>
            <li class="nav-item">
                <a href="" class="nav-link text-muted">
                    いいね
                </a>
            </li>
        </ul>
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

## 2. タブ部分の共通化

+ `$ touch resources/views/users/tabs.blade.php`を実行<br>

+ `server/resources/views/users/tabs.blade.php`を編集<br>

```html:tabs.blade.php
<ul class="nav nav-tabs nav-justified mt-3">
    <li class="nav-item">
        <a href="{{ route('users.show', $user->name) }}" class="nav-link text-muted {{ $hasArticles ? 'active' : '' }}">
            記事
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('users.likes', $user->name) }}" class="nav-link text-muted {{ $hasLikes ? 'active' : '' }}">
            いいね
        </a>
    </li>
</ul>
```

+ [参考演算子 - PHP公式マニュアル](https://www.php.net/manual/ja/language.operators.comparison.php#language.operators.comparison.ternary) <br>

## 3. show.blade.phpでtabs.blade.phpを使用する

+ `server/resources/views/users/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $user->name)

@section('content')
    @include('nav')
    <div class="container">
        @include('users/user')
        @include('users.tabs', ['hasArticles' => true, 'hasLikes' => false])
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

+ [サブビューの読み込み - Laravel公式](https://readouble.com/laravel/6.x/ja/blade.html#including-subviews) <br>

## 4. likes.blade.phpでtabs.blade.phpを使用する

+ `server/resources/views/users/likes.blade.php`を編集<br>

```html:likes.blade.php
@extends('app')

@section('title', $user->name . 'のいいねした記事')

@section('content')
    @include('nav')
    <div class="container">
        @include('users.user')
        @include('users.tabs', ['hasArticles' => false, 'hasLikes' => true])
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

# 9-13 フォロー中・フォロワーの一覧を表示する

## 1. ルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
Route::resource('/articles', ArticleController::class)->except(['index', 'show'])->middleware('auth');
Route::resource('/articles', ArticleController::class)->only('show');
Route::prefix('articles')->name('articles.')->group(function () {
  Route::put('/{article}/like', [ArticleController::class, 'like'])->name('like')->middleware('auth');
  Route::delete('/{article}/like', [ArticleController::class, 'unlike'])->name('unlike')->middleware('auth');
});
Route::get('/tags/{name}', [TagController::class, 'show'])->name('tags.show');
Route::prefix('users')->name('users.')->group(function () {
  Route::get('/{name}', [UserController::class, 'show'])->name('show');
  Route::get('/{name}/likes', [UserController::class, 'likes'])->name('likes');
  // 追加
  Route::get('/{name}/followings', [UserController::class, 'followings'])->name('followings');
  Route::get('/{name}/followers', [UserController::class, 'followers'])->name('followers');
  // ここまで
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
});
```

## 2. アクションメソッドの追加

+ `server/app/Http/Controllers/UserController.php`を編集<br>

```php:UserController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $name)
    {
        $user = User::where('name', $name)->first();

        $articles = $user->articles->sortByDesc('created_at');

        return view('users.show', compact('user', 'articles'));
    }

    public function likes(string $name)
    {
        $user = User::where('name', $name)->first();

        $articles = $user->likes->sortByDesc('created_at');
        // dd($user, $articles);

        return view('users.likes', compact('user', 'articles'));
    }

    // 追加
    public function followings(string $name)
    {
        $user = User::where('name', $name)->first();

        $followings = $user->followings->sortByDesc('created_at');

        return view('users.followings', compact('user', 'followings'));
    }

    public function followers(string $name)
    {
        $user = User::where('name', $name)->first();

        $followers = $user->followers->sortByDesc('created_at');

        return view('users.followers', compact('user', 'followers'));
    }
    // ここまで

    public function follow(Request $request, string $name)
    {
        $user = User::where('name', $name)->first();

        if ($user->id === $request->user()->id) {
            return abort('404', 'Cannot follow yourself.');
        }

        $request->user()->followings()->detach($user);
        $request->user()->followings()->attach($user);

        return ['name' => $name];
    }

    public function unfollow(Request $request, string $name)
    {
        $user = User::where('name', $name)->first();

        if ($user->id === $request->user()->id) {
            return abort('404', 'Cannot follow yourself.');
        }

        $request->user()->followings()->detach($user);

        return ['name' => $name];
    }
}
```
## 3. フォロー中のユーザーの一覧のBladeの作成<br>

+ `$ touch resources/views/users/{followings.blade.php,followers.blade.php}`を実行<br>

+ `server/resources/views/users/followings.blade.php`を編集<br>

```html:followings.blade.php
@extends('app')

@section('title', $user->name . 'のフォロー中')

@section('content')
    @include('nav')
    <div class="container">
        @include('users.user')
        @include('users.tabs', ['hasArticles' => false, 'hasLikes' => false])
        @foreach ($followings as $person)
            @include('users.person')
        @endforeach
    </div>
@endsection
```

## 4. フォロワーの一覧のBladeの作成

+ `server/resources/views/users/followers.blade.php`を編集<br>

```html:followers.blade.php
@extends('app')

@section('title', $user->name . 'のフォロワー')

@section('content')
    @include('nav')
    <div class="container">
        @include('users.user')
        @include('users.tabs', ['hasArticles' => false, 'hasLikes' => false])
        @foreach ($followers as $person)
            @include('users.person')
        @endforeach
    </div>
@endsection
```

## 5. person.blade.phpの作成

+ `$ touch resources/views/users/person.blade.php`を実行<br>

+ `server/resources/views/users/person.blade.php`を編集<br>

```html:person.blade.php
<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex flex-row">
            <a href="{{ route('users.show', $person->name) }}" class="text-dark">
                <i class="fas fa-user-circle fa-3x"></i>
            </a>
            @if (Auth::id() !== $person->id)
                <follow-button class="ml-auto" :initial-is-followed-by='@json($person->isFollowedBy(Auth::user()))'
                    :authorized='@json(Auth::check())' endpoint="{{ route('users.follow', $person->name) }}">
                </follow-button>
            @endif
        </div>
        <h2 class="h5 card-title m-0">
            <a href="{{ route('users.show', $person->name) }}" class="text-dark">
                {{ $person->name }}
            </a>
        </h2>
    </div>
</div>
```

## 6. フォロー中・フォロワーの数から各一覧に遷移可能にする

+ `server/resources/views/users/user.blade.php`を編集<br>

```html:user.blade.php
<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex flex-row">
            <a href="{{ route('users.show', $user->name) }}" class=text-dark>
                <i class="fas fa-user-circle fa-3x"></i>
            </a>
            @if (Auth::id() !== $user->id)
                <follow-button class="ml-auto" :initial-is-followed-by='@json($user->isFollowedBy(Auth::user()))'
                    :authorized='@json(Auth::check())' endpoint="{{ route('users.follow', $user->name) }}">
                </follow-button>
            @endif
        </div>
        <h2 class="h5 card-title m-0">
            <a href="{{ route('users.show', $user->name) }}" class="text-dark">
                {{ $user->name }}
            </a>
        </h2>
    </div>
    <div class="card-body">
        <div class="card-text">
            <a href="{{ route('users.followings', $user->name) }}" class="text-muted"> <!-- 編集 -->
                {{ $user->count_followings }} フォロー
            </a>
            <a href="{{ route('users.followers', $user->name) }}" class="text-muted"> <!-- 編集 -->
                {{ $user->count_followers }} フォロワー
            </a>
        </div>
    </div>
</div>
```

# 10-2 Googleの設定とLaravelの環境変数の設定

+ [一番わかりやすい OAAuth の説明 -Qiita](https://qiita.com/TakahikoKawasaki/items/e37caf50776e00e733be) <br>

## 1. Googleのプロジェクトの作成

まず、[Google API Console](https://console.developers.google.com/project) にアクセスし、<br>

+ プロジェクトの作成<br>

を選択してください。<br>

この通りに設定する [10-2 Googleの設定とLaravelの環境変数の設定](https://www.techpit.jp/courses/11/curriculums/12/sections/104/parts/360) <br>

# 10-3 Laravel Socialiteのインストールと設定ファイルの編集

+ [Laravel Socialite - Laravel公式](https://readouble.com/laravel/6.x/ja/socialite.html) <br>

## 1. Laravel Socialiteのインストール

+ `$ COMPOSER_MEMORY_LIMIT=-1 composer require laravel/socialite`を実行<br>

## 2. Laravelの設定ファイル(config)の編集<br>

+ `server/config/services.php`を編集<br>

```php:services.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    // 追加
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/login/google/callback',
    ]
    //ここまで

];
```

# 10-4 Googleへのリダイレクト処理の作成

## 1. ルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
// 追加
Route::prefix('login')->name('login.')->group(function () {
  Route::get('/{provider}', [LoginController::class, 'redirectToProvider'])->name('{provider}');
});
// ここまで
Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
Route::resource('/articles', ArticleController::class)->except(['index', 'show'])->middleware('auth');
Route::resource('/articles', ArticleController::class)->only('show');
Route::prefix('articles')->name('articles.')->group(function () {
  Route::put('/{article}/like', [ArticleController::class, 'like'])->name('like')->middleware('auth');
  Route::delete('/{article}/like', [ArticleController::class, 'unlike'])->name('unlike')->middleware('auth');
});
Route::get('/tags/{name}', [TagController::class, 'show'])->name('tags.show');
Route::prefix('users')->name('users.')->group(function () {
  Route::get('/{name}', [UserController::class, 'show'])->name('show');
  Route::get('/{name}/likes', [UserController::class, 'likes'])->name('likes');
  Route::get('/{name}/followings', [UserController::class, 'followings'])->name('followings');
  Route::get('/{name}/followers', [UserController::class, 'followers'])->name('followers');
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
});
```

+ `server/app/Http/Controllers/Auth/LoginController.php`を編集<br>

```php:LoginController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite; // 追加

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // 追加
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }
}
```

## 3. ログイン画面のBladeの編集<br>

+ `server/resources/views/auth/login.blade.php`を編集<br>

```html:login.blade.php
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

                        <!-- 追加 -->
                        <a href="{{ route('login.{provider}', ['provider' => 'google']) }}" class="btn btn-block btn-danger">
                            <i class="fab fa-google mr-1"></i>Googleでログイン
                        </a>
                        <!-- ここまで -->

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

                                <div class="text-left">
                                    <a href="{{ route('password.request') }}" class="card-text">パスワードを忘れた方</a>
                                </div>

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
