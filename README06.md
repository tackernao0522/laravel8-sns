# 7-3 いいねテーブルの作成

## 1. likesテーブルのマイグレーションファイルの作成

+ `$ php artisan make:migration create_likes_table --create=likes`を実行<br>

+ `server/database/migrations/create_likes_table.php`を編集<br>

```php:create_likes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('likes');
    }
}
```

`users`テーブルと`articles`テーブルを紐付ける中間テーブルとなっています。<br>

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|いいねを識別するID|
|user_id|整数|いいねしたユーザーのid|
|article_id|整数|いいねしたユーザーのid|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

## 2. likesテーブルの作成<br>

+ `php artisan migrate`を実行<br>

# 7-4 ユーザーが記事をいいね済みかを判定する

## 1. 記事モデルにリレーションを追加する

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

    // 追加
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'likes')->withTimestamps();
    }
}
```

## 2. あるユーザーがいいね済みがどうかを判定するメソッドを作成する

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
}
```
