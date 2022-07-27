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
