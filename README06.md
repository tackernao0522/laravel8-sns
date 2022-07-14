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
