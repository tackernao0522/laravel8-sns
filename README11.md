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
