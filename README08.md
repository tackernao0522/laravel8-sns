# 8-2 タグ入力のVueコンポーネントを表示する

## 1. Vue Tags Inputのインストール

+ `$ npm install -D @johmun/vue-tags-input@2.1.0`を実行<br>

## 2. タグ入力のVueコンポーネントを作成する

+ `$ touch resources/js/components/ArticleTagsInput.vue`を実行<br>

+ `server/resources/js/components/ArticleTagsInput.vue`を編集<br>

```vue:ArticleTagsInput.vue
<template>
  <div>
    <vue-tags-input
      v-model="tag"
      :tags="tags"
      :autocomplete-items="filteredItems"
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
  data() {
    return {
      tag: "",
      tags: [],
      autocompleteItems: [
        {
          text: "Spain",
        },
        {
          text: "France",
        },
        {
          text: "USA",
        },
        {
          text: "Germany",
        },
        {
          text: "China",
        },
      ],
    };
  },
  computed: {
    filteredItems() {
      return this.autocompleteItems.filter((i) => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
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
</style>
```

+ `server/resources/js/app.js`を編集<br>

```js:app.js
import './bootstrap'
import Vue from 'vue'
import ArticleLike from './components/ArticleLike'
import ArticleTagsInput from './components/ArticleTagsInput'

const app = new Vue({
  el: '#app',
  components: {
    ArticleLike,
    ArticleTagsInput,
  }
})
```

+ `server/resources/views/articles/form.blade.php`を編集<br>

```html:form.blade.php
@csrf
<div class="md-form">
    <label>タイトル</label>
    <input type="text" name="title" class="form-control" value="{{ $article->title ?? old('title') }}" required>
</div>
<!-- 追加 -->
<div class="form-group">
    <article-tags-input>
    </article-tags-input>
</div>
<!-- ここまで -->
<div class="form-group">
    <label></label>
    <textarea name="body" class="form-control" rows="16" placeholder="本文" required>{{ $article->body ?? old('body') }}</textarea>
</div>
```

# 8-3 入力されたタグをBladeからPOST送信可能にする

+ `server/resources/js/components/ArticleTagsInput.vue`を編集<br>

```vue:ArticleTagsInput.vue
<template>
  <div>
    <input type="hidden" name="tags" :value="tagsJson" /> // 追加
    <vue-tags-input
      v-model="tag"
      :tags="tags"
      placeholder="タグを5個まで入力できます" // 追加
      :autocomplete-items="filteredItems"
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
  data() {
    return {
      tag: "",
      tags: [],
      autocompleteItems: [
        {
          text: "Spain",
        },
        {
          text: "France",
        },
        {
          text: "USA",
        },
        {
          text: "Germany",
        },
        {
          text: "China",
        },
      ],
    };
  },
  computed: {
    filteredItems() {
      return this.autocompleteItems.filter((i) => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
    },
    // 追加
    tagsJson() {
      return JSON.stringify(this.tags);
    },
    // ここまで
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
</style>
```

JSON.strigifyメソッドを使って、データ tags をJSON形式の文字列に変換したものを返しています。
参考: [JSON.stringify() - MDN](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify) <br>

# 8-4 タグ関連のテーブルを作成する

## 1. タグテーブルのマイグレーションファイルの作成

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|タグを識別するID|
|name|文字列/ユニーク制約|タグ名|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

+ `$ php artisan make:migration create_tags_table --create=tags`を実行<br>

+ `server/database/migrations/create_tags_table.php`を編集<br>

```php:create_tags_table.blade.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
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
        Schema::dropIfExists('tags');
    }
}
```

参考: [インデックス作成 - Laravel公式](https://readouble.com/laravel/6.x/ja/migrations.html#creating-indexes) <br>

## 2. 記事とタグの中間テーブルのマイグレーションファイルの作成

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|タグの紐付けを識別するID|
|article_id|整数|タグが付けられた記事のid|
|tag_id|整数|記事に付けられたタグのid|
|created_at|日付と時刻|作成日時|
|updated_st|日付と時刻|更新日時|

+ `$ php artisan make:migration create_article_tag_table --create=article_tag`を実行<br>

+ `server/database/migrations/create_article_tag_table.php`を編集<br>

```php:create_article_tag_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('article_tag');
    }
}
```

## 3. テーブルをデータベースに作成する

+ `$ php artisan migrate`を実行<br>

# タグモデルの作成と記事モデルの編集

## 1. タグモデルの作成

+ `$ php artisan make:model Tag`を実行<br>

## 2. 記事モデルからタグモデルへのリレーションの作成

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

    public function getCountLikesAttribute(): int
    {
        return $this->likes->count();
    }

    // 追加
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Tag')->withTimestamps();
    }
}
```

# 8-6 フォームリクエストの編集

## 1. バリデーションルールの追加

+ `server/app/Http/Requests/ArticleRequest.php`を編集<br>

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
            // 追加
            'tags' => 'json|regex:/^(?!.*\s).+$/ulregex:/^(?!.*\/).*$/u',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            // 追加
            'tags' => 'タグ',
        ];
    }
}
```

参考: [json - Laravel](https://readouble.com/laravel/6.x/ja/validation.html#rule-json) <br>

+ [regex:正規表現 - Laravel](https://readouble.com/laravel/6.x/ja/validation.html#rule-regex) <br>
+ [【5分でまるっと理解】 PHP正規表現の使い方まとめ](https://eng-entrance.com/php-regularex) <br>
+ [preg_match - PHP公式マニュアル](https://www.php.net/manual/ja/function.preg-match.php) <br>

## 2. tagsの整形を行う

+ `server/app/Http/Requests/ArticleRequest.php`を編集<br>

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
            'tags' => 'json|regex:/^(?!.*\s).+$/u|regex:/^(?!.*\/).*$/u',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            'tags' => 'タグ',
        ];
    }

    // 追加
    public function passedValidation()
    {
        $this->tags = collect(json_decode($this->tags))
            ->slice(0, 5)
            ->map(function ($requestTag) {
                return $requestTag->text;
            });
    }
}
```

※ _passedValidationメソッド_ <br>

`passedValidation`メソッドは、フォームリクエストのバリデーションが成功した後に自動的に呼ばれるメソッドです。<br>

バリデーション成功後に何と処理をしたければ、ここに処理を書きます。<br>

※ _json_decode関数_ <br>

まず、`json_decode($this->tags)`で、JSON形式の文字列であるタグ情報をPHPの`json_decode`関数を使って連想配列に変換しています。<br>

+ [json_decode - PHP公式マニュアル](https://www.php.net/manual/ja/function.json-decode.php) <br>

※ _collect関数_ <br>

それをさらにLaravelの `collect`関数を使ってコレクションに変換しています。<br>

コレクションに変換する理由は、この後で行う`slice`メソッドや`map`メソッドといった、便利なコレクションメソッドを使うためです。<br>

+ [コレクション生成 -Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#creating-collections) <br>

※ _sliceメソッド_ <br>

`slice`メソッドを使うと、コレクションの要素が、第一引数に指定したインデックスから第二引数に指定した数だけになります。<br>

`slice(0, 5)`にすると、もしコレクションの要素が6個以上あったとしても、最初の5個だけが残ります。<br>

8章のパート3では、タグ入力フォームに `"タグを5個まで入力できます"`と表示するようにしましたが、その対応をここで行っています。<br>

+ [slice() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-slice)

※ _mapメソッド_ <br>

`map`メソッドは、コレクションの各要素に対して順に処置を行い、新しいコレクションを作成します。<br>

この`map`メソッドには、引数に関数を渡すことができます。<br>

このような、引数に渡す関数のことをコールバックと呼びます。<br>

今回のコールバックは以下のクロージャー（無名関数）となっています。<br>

```php:sample.php
function ($requestTag) {
  // 処理
}
```

+ [map() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-map) <br>
