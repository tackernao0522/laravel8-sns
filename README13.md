# 9-7 フォローでテーブルを更新して結果をレスポンスする

## 1. フォロー機能のルーティングを追加する

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
  // 追加
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
  // ここまで
});
```

+ [ルートグループ - Laravel公式](https://readouble.com/laravel/6.x/ja/routing.html#route-groups) <br>

## 2. コントローラーにフォロー機能のアクションメソッドを追加する

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

    // 追加
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
    // ここまで
}
```

+ [where - Laraval公式](https://readouble.com/laravel/6.x/ja/collections.html#method-where) <br>

+ [first() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-first) <br>

+ [abort() - Laravel公式](https://readouble.com/laravel/6.x/ja/helpers.html#method-abort) <br>

+ [HTTP レスポンスステータスコード - HTTP | MDN](https://developer.mozilla.org/ja/docs/Web/HTTP/Status) <br>

+ [attach/detach - Laravel公式](https://readouble.com/laravel/6.x/ja/eloquent-relationships.html#updating-many-to-many-relationships) <br>

## 3. リレーションの追加

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
    public function followings(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'follows', 'follower_id', 'followee_id')->withTimestamps();
    }

    public function isFollowedBy(?User $user): bool
    {
        return $user
            ? (bool)$this->followers->where('id', $user->id)->count()
            : false;
    }
}
```

# 9-8 フォローボタンのVueコンポーネントからLaravelに非同期通信する

## 1. ログイン状態と非同期通信先URLをBladeからVueコンポーネントに渡す

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

+ [現在のユーザーが認証されているか調べる - Laravel公式](https://readouble.com/laravel/6.x/ja/authentication.html#retrieving-the-authenticated-user) <br>

## 2. Vueコンポーネントからフォロー・フォロー解除を行う

+ `server/resources/js/components/FollowButton.vue`を編集<br>

```vue:FollowButton.vue
<template>
  <div>
    <button
      class="btn-sm shadow-none border border-primary p-2"
      :class="buttonColor"
      @click="clickFollow" // 追加
    >
      <i class="mr-1" :class="buttonIcon"></i>
      {{ buttonText }}
    </button>
  </div>
</template>

<script>
export default {
  props: {
    initialIsFollowedBy: {
      type: Boolean,
      default: false,
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
      isFollowedBy: this.initialIsFollowedBy,
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
  // 追加
  methods: {
    clickFollow() {
      if (!this.authorized) {
        alert("フォロー機能はログイン中のみ使用できます");
        return;
      }

      this.isFollowedBy ? this.unfollow() : this.follow();
    },
    async follow() {
      const response = await axios.put(this.endpoint);

      this.isFollowedBy = true;
    },
    async unfollow() {
      const response = await axios.delete(this.endpoint);

      this.isFollowedBy = false;
    },
  },
  // ここまで
};
</script>
```

+ [条件(三項)演算子 - MDN](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Operators/Conditional_Operator) <br>

+ [async/await入門 - async/awitとは | CodeGrid](https://app.codegrid.net/entry/2017-async-await-1) <br>
