# 9-3 フォローボタンを表示する

## 1. フォローボタンのVueコンポーネントを作成する

+ `$ touch resources/js/components/FollowButton.vue`を実行<br>

+ `server/resources/js/components/FollowButton.vue`を編集<br>

```vue:FollowButton.vue
<template>
  <div>
    <button
      class="btn-sm shadow-none border border-primary p-2"
      :class="buttonColor"
    >
      <i class="mr-1" :class="buttonIcon"></i>
      {{ buttonText }}
    </button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isFollowedBy: false,
    };
  },
  computed: {
    buttonColor() {
      return this.isFollowedBy ? "bg-primary text-white" : "bg-white";
    },
    buttonIcon() {
      return this.isFollowedBy ? "fas fa-user-check" : "fas fa-user-plus";
    },
    buttonText() {
      return this.isFollowedBy ? "フォロー中" : "フォロー";
    },
  },
};
</script>
```

+ `server/resources/js/app.js`を編集<br>

```js:app.js
import './bootstrap'
import Vue from 'vue'
import ArticleLike from './components/ArticleLike'
import ArticleTagsInput from './components/ArticleTagsInput'
import FollowButton from './components/FollowButton' // 追加

const app = new Vue({
  el: '#app',
  components: {
    ArticleLike,
    ArticleTagsInput,
    FollowButton // 追加
  }
})
```

## 3. ユーザーページのBladeの編集

+ `server/resources/views/users/show.blade.php`を編集<br>

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
                    <!-- 追加 -->
                    @if (Auth::id() !== $user->id)
                        <follow-button class="ml-auto"></follow-button>
                    @endif
                    <!-- ここまで -->
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

# 9-4 フォローを管理するテーブルの作成

## 1. followテーブルのマイグレーションファイルの作成

|カラム名|属性|役割|
|:---:|:---:|:---:|
|id|整数|フォロワー・被フォローの紐付けを識別するID|
|follower_id|整数|フォロワーのユーザーid|
|followee_id|整数|フォローされている側のユーザーid|
|created_at|日付と時刻|作成日時|
|updated_at|日付と時刻|更新日時|

+ `$ php artisan make:migration create_follows_table --create=follows`を実行<br>

+ `server/database/migrations/create_follows_table.php`を編集<br>

```php:create_follows_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('follower_id')->unsigned();
            $table->foreign('follower_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->bigInteger('followee_id')->unsigned();
            $table->foreign('followee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('follows');
    }
}
```

+ `$ php artisan migrate`を実行<br>

# 9-5 フォロー中かどうかを判定する

## 1. ユーザーモデルにリレーションを追加する

+ `server/app/Models/User.php`を編集<br>

```php:User.php
<?php

namespace App\Models;

use App\Mail\BareMail;
use App\Notifications\PasswordResetNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token, new BareMail()));
    }

    // 追加
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'follows', 'followee_id', 'follower_id')->withTimestamps();
    }
}
```

## 2. あるユーザーをフォロー中かどうか判定するメソッドを作成する

+ `server/app/Models/User.php`を編集<br>

```php:User.php
<?php

namespace App\Models;

use App\Mail\BareMail;
use App\Notifications\PasswordResetNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token, new BareMail()));
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'follows', 'followee_id', 'follower_id')->withTimestamps();
    }

    // 追加
    public function isFollowedBy(?User $user): bool
    {
        return $user
            ? (bool)$this->followers->where('id', $user->id)->count()
            : false;
    }
}
```

# 9-6 フォローボランのVueコンポーネントにBlade経由で値を渡す

## 1. Bladeでのv-bindの使用

+ `server/resources/views/users/show.blade.php`を編集<br>

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
                    @if (Auth::id() !== $user->id)
                        <!-- 編集 -->
                        <follow-button class="ml-auto" :initial-is-followed-by='@json($user->isFollowedBy(Auth::user()))'>
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

`:initial-is-followed-by` は、 `v-bind:initial-is-followed-by` の省略形です。<br>

また、`@json`を使うことで、`$user->isFollowedBy(Aut::user())`の結果を値ではなく文字列としてVueコンポーネントに渡しています。<br>

+ [JSONのレンダー - Laravel公式](https://readouble.com/laravel/6.x/ja/blade.html) <br>

## 2. Vueコンポーネントの編集

+ `server/resources/js/components/FollowButton.vue`を編集<br>

```vue:FollowButton.vue
<template>
  <div>
    <button
      class="btn-sm shadow-none border border-primary p-2"
      :class="buttonColor"
    >
      <i class="mr-1" :class="buttonIcon"></i>
      {{ buttonText }}
    </button>
  </div>
</template>

<script>
export default {
  // 追加
  props: {
    initialIsFollowedBy: {
      type: Boolean,
      default: false,
    },
  // ここまで
  },
  data() {
    return {
      isFollowedBy: this.initialIsFollowedBy, // 編集
    };
  },
  computed: {
    buttonColor() {
      return this.isFollowedBy ? "bg-primary text-white" : "bg-white";
    },
    buttonIcon() {
      return this.isFollowedBy ? "fas fa-user-check" : "fas fa-user-plus";
    },
    buttonText() {
      return this.isFollowedBy ? "フォロー中" : "フォロー";
    },
  },
};
</script>
```

+ [単方向のデータフロー - Vue.js](https://jp.vuejs.org/v2/guide/components-props.html#%E5%8D%98%E6%96%B9%E5%90%91%E3%81%AE%E3%83%87%E3%83%BC%E3%82%BF%E3%83%95%E3%83%AD%E3%83%BC) <br>

## 3.1. データベース(MySql)に接続する

+ [docker MySQL 接続参考](https://techtechmedia.com/docker-mysql-connection/) <br>

+ `docker ps`を実行<br>

```:terminal
CONTAINER ID   IMAGE                   COMMAND                  CREATED       STATUS       PORTS                                            NAMES
393f019f9762   laravel-sns_php         "docker-php-entrypoi…"   4 hours ago   Up 4 hours   9000/tcp                                         laravel-sns_php_run_a1587637e24a
c9dc8dafb2d6   laravel-sns_php         "docker-php-entrypoi…"   4 hours ago   Up 4 hours   9000/tcp                                         laravel-sns_php_run_61bcd466980d
cc99c91b4e1a   nginx                   "/docker-entrypoint.…"   4 hours ago   Up 4 hours   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp         nginx
bac170cd7f10   mysql:8.0               "docker-entrypoint.s…"   4 hours ago   Up 4 hours   0.0.0.0:3306->3306/tcp, 33060/tcp                laravel8snsdb-host
cbc38061e946   laravel-sns_php         "docker-php-entrypoi…"   4 hours ago   Up 4 hours   9000/tcp                                         php
baa7fbdd60f9   mailhog/mailhog         "MailHog"                4 hours ago   Up 4 hours   0.0.0.0:1025->1025/tcp, 0.0.0.0:8025->8025/tcp   laravel-sns-mailhog-1
028674096ad0   phpmyadmin/phpmyadmin   "/docker-entrypoint.…"   4 hours ago   Up 4 hours   0.0.0.0:8080->80/tcp                             phpmyadmin
```

+ `$ docker exec -it laravel8snsdb-host bash`を実行<br>

+ `bash-4.4# mysql -u root -p`を実行<br>

## 3.2. usersテーブルの状態を確認する

+ `mysql> show databases;`を実行<br>

```
+---------------------+
| Database            |
+---------------------+
| information_schema  |
| laravl8sns-database |
| mysql               |
| performance_schema  |
| sys                 |
+---------------------+
5 rows in set (0.00 sec)
```
+ `mysql> use laravl8sns-database;`を実行<br>

+ `mysql> show tables;`を実行<br>

+ `mysql> SELECT id, name FROM users ORDER BY id;`を実行<br>

```
+----+--------+
| id | name   |
+----+--------+
|  1 | takaki |
|  2 | naomi  |
+----+--------+
2 rows in set (0.01 sec)
```
## 3.3 followers テーブルにデータを新規登録する

+ `mysql> INSERT INTO follows (follower_id, followee_id, created_at, updated_at) VALUES (1, 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);`を実行<br>

```
Query OK, 1 row affected (0.01 sec)
```

+ `mysql> \q`を実行して抜ける<br>
