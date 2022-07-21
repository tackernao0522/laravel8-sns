# 8-7 記事投稿処理とタグモデルの編集

## 1. 記事投稿処理でタグの登録と記事・タグの紐付けを行う

+ `server/app/Http/Controllers/ArticleController.php`を編集<br>

```php:ArticleController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\Tag; // 追加
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

        $request->tags->each(function ($tagName) use ($article) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $article->tags()->attach($tag);
        });

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
}
```

### eachメソッドの使用

`$request->tags` の内容は、全パートでフォームリクエスト(ArticleRequest)に追加した `passedValidation` メソッドによって、コレクションになっています。<br>

そのため、コレクションメソッドである `each` メソッドを使うことができます。<br>

`each`メソッドは、コレクションの各要素に対して順に処理を行うことができます。<br>

また、この`each`メソッドには、引数にコールバック(関数)を渡すことができます。<br>

+ [each() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-each) <br>

### クロージャ(無名関数)の引数とuseについて

```php:sample.php
$request->tags->each(function ($tagName) use ($article) {
  // 略
});
```

今回、`each`メソッドに渡すコールバックは、クロージャ（無名関数）としています。<br>

クロージャの第一引数にはコレクションの値が、第二引数にはコレクションのキーが入ります。<br>

もし、`$request->tags` の内容が、<br>

```php:sample.php
['USA', 'France']
```

といったコレクションであれば、`each`メソッドによる繰り返し処理の1回目では、<br>

+ クロージャの第一引数は、 `'USA'`<br>
+ クロージャの第二引数は、 `0`<br>
となります。<br>

`each`メソッドによる繰り返し処理の2回目では、<br>

+ クロージャの第一引数は、'`France`'<br>
+ クロージャの第二引数は `1`<br>
となります。<br>

クロージャの引数の名前は何でも良いのですが、第一引数はその内容が分かりやすくなるよう、`$tagName` としました。<br>

第二引数は今回のクロージャの中の処理で特に使わないので、省略しています。<br>

`use ($article)` とあるのは、クロージャの中の処理で変数 `$article` を使うためです。<br>

クロージャの中では、クロージャの外側で定義されている変数を通常使用できません。<br>

使用したい場合は、`use (変数名, 変数名, ...)`といったように、使う変数名を記述する必要があります。<br>

+ [無名関数(クロージャ) - PHP公式マニュアル](https://www.php.net/manual/ja/functions.anonymous.php#functions.anonymous) <br>


### タグの登録と記事・タグの紐付け

記事と同時にタグを登録するにあたり、考慮すべきことはそのタグが既に `tags` テーブルに存在するタグか、全く新規のタグか、ということです。<br>

既に `tags` テーブルに存在するタグであれば、tags テーブルに登録する必要はなく、記事とタグの紐付けのみを行えば良い( article_tag テーブルにレコードを保存するだけで良い)ことになります。<br>

そこで、タグの登録には `firstOrCreate`メソッドを使います。<br>

```php:sample.php
$tag = Tag::firstOrCreate(['name' => $tagName]);
```

`firstOrCreate`メソッドは、引数として渡した「カラム名と値のペア」を持つレコードがテーブルに存在するかどうかを探し、もし存在すればそのモデルを返します。<br>

テーブルに存在しなければ、そのレコードをテーブルに保存した上で、モデルを返します。<br>

+ [firsOrCreate - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent.html#other-creation-methods) <br>

いずれにしても、変数 `$tag` にはタグモデルが代入されますので、後は<br>

```php:sample.php
$article->tags()->attach($tag);
```

とすることで、記事とタグの紐付け(`article_tag` テーブルへのレコードの保存)が行われます。<br>

`each`メソッドによる繰り返し処理によって、これが記事投稿画面で入力されたタグの数だけ行われます。<br>

## 2. タグモデルでタグ名の保存を許可する

+ `server/app/Models/Tag.php`を編集<br>

```php:Tag.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
```

+ [複数代入 - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent.html#mass-assignment) <br>

# 8-8 記事へのタグ表示とタグ登録の動作確認

## 1. 記事一覧・記事詳細画面にタグを表示する

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
        <a href="" class="border p-1 mr-1 mt-1 text-muted">
            {{ $tag->name }}
        </a>
        @if ($loop->last)
</div>
</div>
@endif
@endforeach
</div>
```

+ [ループ変数 - Laravel公式](https://readouble.com/laravel/6.x/ja/blade.html#the-loop-variable) <br>
