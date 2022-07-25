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