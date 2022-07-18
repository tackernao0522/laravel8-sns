# 7-6 いいね数を算出して Blade を通じて Vue を渡す

## 1. 現在のいいね数を算出するメソッドを作る

+ `server/app/Models/Article.php`を編集<br>

```php:Article.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'likes')->withTimestamps();
    }

    public function isLikedBy(?User $user): bool
    {
        return $user
            ? (bool)$this->likes->where('id', $user->id)->count()
            : false;
    }

    // 追加
    public function getCountLikesAttribute(): int
    {
        return $this->likes->count();
    }
}
```

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
            <!-- 編集 -->
            <article-like :initial-is-liked-by="@json($article->isLikedBy(Auth::user()))"
                :initial-count-likes="@json($article->count_likes)">
            </article-like>
        </div>
    </div>
</div>
```

+ `server/resources/js/components/ArticleLike.vue`を編集<br>

```vue:ArticleLike.vue
<template>
  <div>
    <button type="button" class="btn m-0 p-1 shadow-none">
      <i class="fas fa-heart mr-1" :class="{ 'red-text': this.isLikedBy }" />
    </button>
    // 追加
    {{ countLikes }}
  </div>
</template>

<script>
export default {
  props: {
    initialIsLikedBy: {
      type: Boolean,
      dafault: false,
    },
    // 追加
    initialCountLikes: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      isLikedBy: this.initialIsLikedBy,
      countLikes: this.initialCountLikes, // 追加
    };
  },
};
</script>
```

# 7-7 いいねでテーブルを更新して結果をレスポンスする

## 1. いいね機能のルーティングを追加する

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
Route::resource('/articles', ArticleController::class)->except(['index', 'show'])->middleware('auth');
Route::resource('/articles', ArticleController::class)->only('show');
// 追加
Route::prefix('articles')->name('articles.')->group(function () {
  Route::put('/{article}/like', [ArticleController::class, 'like'])->name('like')->middleware('auth');
  Route::delete('/{article}/like', [ArticleController::class, 'unlike'])->name('unlike')->middleware('auth');
});
// ここまで
```

## 2. コントローラにいいね機能のアクションメソッドを追加する

+ `server/app/Http/Controllers/ArticleController.php`を編集<br>

```php:ArticleController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }

    public function index()
    {
        $articles = Article::all()->sortByDesc('created_at');

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all());
        $article->user_id = $request->user()->id;
        $article->save();

        return redirect()->route('articles.index');
    }

    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all())->save();

        return redirect()->route('articles.index');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('articles.index');
    }

    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }

    // 追加
    public function like(Request $request, Article $article)
    {
        $article->likes()->detach($request->user()->id);
        $article->likes()->attach($request->user()->id);

        return [
            'id' => $article->id,
            'countLikes' => $article->count_likes
        ];
    }

    public function unlike(Request $request, Article $article)
    {
        $article->likes()->detach($request->user()->id);

        return [
            'id' => $article->id,
            'countLikes' => $article->count_likes,
        ];
    }
    // ここまで
}
```

[attach/detach - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html#updating-many-to-many-relationships) <br>

# 7-8 VueからLaravelに非同期通信する

## 1. ログイン状態と非同期通信URLをBladeからVueコンポーネントに渡す

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
            <!-- 編集 -->
            <article-like :initial-is-liked-by="@json($article->isLikedBy(Auth::user()))"
                :initial-count-likes="@json($article->count_likes)" :authorized='@json(Auth::check())'
                endpoint="{{ route('articles.like', $article->id) }}">
            <!-- ここまで -->
            </article-like>
        </div>
    </div>
</div>
```

+ `server/resources/js/components/ArticleLike.vue`を編集<br>

```vue:ArticleLike.vue
<template>
  <div>
    <button type="button" class="btn m-0 p-1 shadow-none">
      <i
        class="fas fa-heart mr-1"
        :class="{ 'red-text': this.isLikedBy }"
        @click="clickLike" // 追加
      />
    </button>
    {{ countLikes }}
  </div>
</template>

<script>
export default {
  props: {
    initialIsLikedBy: {
      type: Boolean,
      dafault: false,
    },
    initialCountLikes: {
      type: Number,
      default: 0,
    },
    // 追加
    authorized: {
      type: Boolean,
      default: false,
    },
    endpoint: {
      type: String,
    },
    // ここまで
  },
  data() {
    return {
      isLikedBy: this.initialIsLikedBy,
      countLikes: this.initialCountLikes,
    };
  },
  // 追加
  methods: {
    clickLike() {
      if (!this.authorized) {
        alert("いいね機能はログイン中のみ使用できます");
        return;
      }
      this.isLikedBy ? this.unlike() : this.like();
    },
    async like() {
      const response = await axios.put(this.endpoint);

      this.isLikedBy = true;
      this.countLikes = response.data.countLikes;
    },
    async unlike() {
      const response = await axios.delete(this.endpoint);

      this.isLikedBy = false;
      this.countLikes = response.data.countLikes;
    },
  },
  // ここまで
};
</script>
```

[条件（三項）演算子 - MDN](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Operators/Conditional_Operator) <br>

[async/await入門 - async/awaitとは | CodeGrid](https://app.codegrid.net/entry/2017-async-await-1) <br>


+ `server/resources/views/nav.blade.php`を修正<br>

```html:nav.blade.php
<!-- 省略 -->
@auth
    {{-- Dropdown --}}
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="fas fa-user-circle"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right dropdown-primary" aria-labelledby="navbarDropdownMenuLink">
            <!-- 修正 -->
            <button class="dropdown-item" type="button" onclick="location.href=''">
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
<!-- 省略 -->
```
