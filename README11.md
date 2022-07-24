# 8-12 タグごとの記事一覧画面を作る

## 2. タグ別記事一覧画面のルーティングを定義する

+ `$ php artisan make:controller TagController`を実行<br>

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\TagController; // 追加
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
Route::get('/tags/{name}', [TagController::class, 'show'])->name('tags.show'); // 追加
```

## 3. タグモデルに記事モデルへのリレーションを追加する

+ `server/app/Models/Tag.php`を編集<br>

```php:Tag.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // 追加

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function getHashtagAttribute(): string
    {
        return '#' . $this->name;
    }

    // 追加
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Article')->withTimestamps();
    }
}
```

+ [リレーション - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html) <br>

## 4. コントローラにタグ別記事一覧画面のアクションメソッドを作成する

+ `server/app/Http/Controllers/TagController.php`を編集<br>

```php:TagController.php
<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function show(string $name)
    {
        $tag = Tag::where('name', $name)->first();

        return view('tags.show', compact('tag'));
    }
}
```

+ [first() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-first) <br>

## 5. タグ別記事一覧画面のBladeを作成する

+ `$ mkdir resources/views/tags && touch $_/show.blade.php`を実行<br>

+ `server/resoruces/views/tags/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $tag->hashtag)

@section('content')
    @include('nav')
    <div class="container">
        <div class="card mt-3">
            <div class="card-body">
                <h2 class="h4 card-title m-0">{{ $tag->hashtag }}</h2>
                <div class="card-text text-right">
                    {{ $tag->articles->count() }}件
                </div>
            </div>
        </div>
        @foreach ($tag->articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

## 6. 各記事のタグからタグ別記事一覧へ遷移可能にする

+ `server/resources/views/articles/card.blade.php`を編集<br>

```html:card.blade.php
<div class="card mt-3">
    <div class="card-body d-flex flex-row">
        <i class="fas fa-user-circle fa-3x mr-1"></i>
        <div>
            <div class="font-weight-bold">{{ $article->user->name }}</div>
            <div class="font-weight-lighter">{{ $article->created_at->format('Y/m/d H:i') }}</div>
        </div>

        @if (Auth::id() === $article->user_id)
            <!-- dropdown -->
            <div class="ml-auto card-text">
                <div class="dropdown">
                    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('articles.edit', $article->id) }}">
                            <i class="fas fa-pen mr-1"></i>記事を更新する
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" data-toggle="modal"
                            data-target="#modal-delete-{{ $article->id }}">
                            <i class="fas fa-trash-alt mr-1"></i>記事を削除する
                        </a>
                    </div>
                </div>
            </div>
            <!-- dropdown -->

            <!-- modal -->
            <div id="modal-delete-{{ $article->id }}" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('articles.destroy', $article->id) }}">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body">
                                {{ $article->title }}を削除します。よろしいですか？
                            </div>
                            <div class="modal-footer justify-content-between">
                                <a class="btn btn-outline-grey" data-dismiss="modal">キャンセル</a>
                                <button type="submit" class="btn btn-danger">削除する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- modal -->
        @endif

    </div>
    <div class="card-body pt-0 pb-2">
        <h3 class="h4 card-title">
            <a class="text-dark" href="{{ route('articles.show', ['article' => $article]) }}">
                {{ $article->title }}
            </a>
        </h3>
        <div class="card-text">
            {!! nl2br(e($article->body)) !!}
        </div>
    </div>
    <div class="card-body pt-0 pb-2 pl-3">
        <div class="card-text">
            <article-like :initial-is-liked-by="@json($article->isLikedBy(Auth::user()))"
                :initial-count-likes="@json($article->count_likes)" :authorized='@json(Auth::check())'
                endpoint="{{ route('articles.like', $article->id) }}">
            </article-like>
        </div>
    </div>
    @foreach ($article->tags as $tag)
        @if ($loop->first)
            <div class="card-body pt-0 pb-4 pl-3">
                <div class="card-text line-height">
        @endif
        <!-- 編集 -->
        <a href="{{ route('tags.show', $tag->name) }}" class="border p-1 mr-1 mt-1 text-muted">
            {{ $tag->hashtag }}
        </a>
        @if ($loop->last)
</div>
</div>
@endif
@endforeach
</div>
```

# 8-13 タグ入力フォームの確定キー種類をカスタマイズする

## VueTagsInputコンポーネントにキーコードを渡す

+ [Props - Vue Tags Input](http://www.vue-tags-input.com/#/api/props) <br>

+ [JavaScript キーコードの一覧](https://javascript.programmer-reference.com/js-list-keycode/) <br>

+ `server/resources/js/components/ArticleTagsInput.vue`を編集<br>

```vue:ArticleTagsInput.vue
<template>
  <div>
    <input type="hidden" name="tags" :value="tagsJson" />
    <vue-tags-input
      v-model="tag"
      :tags="tags"
      placeholder="タグを5個まで入力できます"
      :autocomplete-items="filteredItems"
      :add-on-key="[13, 32]" // 追加
      @tags-changed="(newTags) => (tags = newTags)"
    />
  </div>
</template>

<script>
import VueTagsInput from "@johmun/vue-tags-input";

export default {
  components: {
    VueTagsInput,
  },
  props: {
    initialTags: {
      type: Array,
      default: [],
    },
    autocompleteItems: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {
      tag: "",
      tags: this.initialTags,
    };
  },
  computed: {
    filteredItems() {
      return this.autocompleteItems.filter((i) => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
    },
    tagsJson() {
      return JSON.stringify(this.tags);
    },
  },
};
</script>

<style lang="css" scoped>
.vue-tags-input {
  max-width: inherit;
}
</style>
<style lang="css">
.vue-tags-input .ti-tag {
  background: transparent;
  border: 1px solid #747373;
  color: #747373;
  margin-right: 4px;
  border-radius: 0px;
  font-size: 13px;
}
.vue-tags-input .ti-tag::before {
  content: "#";
}
</style>
```

# 9-2 ユーザーページを表示する

## 1. コントローラの作成

+ `$ php artisan make:controller UserController`を実行<br>

## 2. ルーティングの追加

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
// 追加
Route::prefix('users')->name('users.')->group(function () {
  Route::get('/{name}', [UserController::class, 'show'])->name('show');
});
```

+ [ルートグループ - Laravel公式](https://readouble.com/laravel/6.x/ja/routing.html#route-groups) <br>

## 3. アクションメソッドの追加

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

        return view('users.show', compact('user'));
    }
}
```

## 4. ユーザーページのBladeの作成

+ `$ mkdir resources/views/users && touch $_/show.blade.php`を実行<br>

+ `resources/views/users/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $user->name)

@section('content')
    @include('nav')
    <div class="container">
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex flex-row">
                    <a href="{{ route('users.show', $user->name) }}" class=text-dark>
                        <i class="fas fa-user-circle fa-3x"></i>
                    </a>
                </div>
                <h2 class="h5 card-title m-0">
                    <a href="{{ route('users.show', $user->name) }}" class="text-dark">
                        {{ $user->name }}
                    </a>
                </h2>
            </div>
            <div class="card-body">
                <div class="card-text">
                    <a href="" class="text-muted">
                        10 フォロー
                    </a>
                    <a href="" class="text-muted">
                        10 フォロワー
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
```

## 5. 記事からユーザーページに遷移可能にする

+ `server/resources/views/articles/card.blade.php`を編集<br>

```html:card.blade.php
<div class="card mt-3">
    <div class="card-body d-flex flex-row">
        <!-- 編集 -->
        <a href="{{ route('users.show', $article->user->name) }}" class="text-dark">
            <i class="fas fa-user-circle fa-3x mr-1"></i>
        </a>
        <!-- ここまで -->
        <div>
            <div class="font-weight-bold">
                <!-- 編集 -->
                <a href="{{ route('users.show', $article->user->name) }}" class="text-dark">
                    {{ $article->user->name }}
                </a>
                <!-- ここまで -->
            </div>
            <div class="font-weight-lighter">
                {{ $article->created_at->format('Y/m/d H:i') }}
            </div>
        </div>

        @if (Auth::id() === $article->user_id)
            <!-- dropdown -->
            <div class="ml-auto card-text">
                <div class="dropdown">
                    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('articles.edit', $article->id) }}">
                            <i class="fas fa-pen mr-1"></i>記事を更新する
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" data-toggle="modal"
                            data-target="#modal-delete-{{ $article->id }}">
                            <i class="fas fa-trash-alt mr-1"></i>記事を削除する
                        </a>
                    </div>
                </div>
            </div>
            <!-- dropdown -->

            <!-- modal -->
            <div id="modal-delete-{{ $article->id }}" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('articles.destroy', $article->id) }}">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body">
                                {{ $article->title }}を削除します。よろしいですか？
                            </div>
                            <div class="modal-footer justify-content-between">
                                <a class="btn btn-outline-grey" data-dismiss="modal">キャンセル</a>
                                <button type="submit" class="btn btn-danger">削除する</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- modal -->
        @endif

    </div>
    <div class="card-body pt-0 pb-2">
        <h3 class="h4 card-title">
            <a class="text-dark" href="{{ route('articles.show', ['article' => $article]) }}">
                {{ $article->title }}
            </a>
        </h3>
        <div class="card-text">
            {!! nl2br(e($article->body)) !!}
        </div>
    </div>
    <div class="card-body pt-0 pb-2 pl-3">
        <div class="card-text">
            <article-like :initial-is-liked-by="@json($article->isLikedBy(Auth::user()))"
                :initial-count-likes="@json($article->count_likes)" :authorized='@json(Auth::check())'
                endpoint="{{ route('articles.like', $article->id) }}">
            </article-like>
        </div>
    </div>
    @foreach ($article->tags as $tag)
        @if ($loop->first)
            <div class="card-body pt-0 pb-4 pl-3">
                <div class="card-text line-height">
        @endif
        <a href="{{ route('tags.show', $tag->name) }}" class="border p-1 mr-1 mt-1 text-muted">
            {{ $tag->hashtag }}
        </a>
        @if ($loop->last)
</div>
</div>
@endif
@endforeach
</div>
```

## 6. マイページメニューからユーザーページに遷移可能にする

+ `server/resources/views/nav.blade.php`を編集<br>

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
                    <!-- 編集 -->
                    <button class="dropdown-item" type="button"
                        onclick="location.href='{{ route('users.show', Auth::user()->name) }}'">
                        <!-- ここまで -->
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
