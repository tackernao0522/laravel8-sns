# 6-2 パスワード再設定メール要求画面の作成

+ `server/resources/views/auth/passwords/email.blade.php`を編集<br>

```html:email.blade.php
@extends('app')

@section('title', 'パスワード再設定')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">パスワード再設定</h2>

                        @include('error_card_list')

                        @if (session('status'))
                            <div class="card-text alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="card-text">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf

                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" id="email" name="email" class="form-control" required>
                                </div>

                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">メール送信</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

## 4. ログイン画面からパスワード再設定メール要求画面へ遷移可能にする

+ `server/resources/views/auth/login.blade.php`を編集<br>

```html:login.blade.php
@extends('app')

@section('title', 'ログイン')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">ログイン</h2>

                        @include('error_card_list')

                        <div class="card-text">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" id="email" name="email" class="form-control"
                                        value="{{ old('email') }}" required>
                                </div>

                                <div class="md-form">
                                    <label for="password">パスワード</label>
                                    <input class="form-control" type="password" id="password" name="password" required>
                                </div>

                                <input type="hidden" name="remember" id="remember" value="on">

                                <!-- 追加 -->
                                <div class="text-left">
                                    <a href="{{ route('password.request') }}" class="card-text">パスワードを忘れた方</a>
                                </div>
                                <!-- ここまで -->

                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">ログイン</button>
                            </form>

                            <div class="mt-0">
                                <a href="{{ route('register') }}" class="card-text">ユーザー登録はこちら</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

# 6-4 パスワード再設定関連のメッセージの日本語化

+ $ touch resources/lang/ja/password.php`を実行<br>

+ `server/resources/lang/ja/passwords.php`を編集<br>

```php:password.php
<?php

return [

  /*
    |--------------------------------------------------------------------------
    | パスワードリセット言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は既存のパスワードを無効にしたい場合に、無効なトークンや
    | 新しいパスワードが入力された場合のように、パスワードの更新に失敗した
    | 理由を示すデフォルトの文言です。
    |
    */

  'reset' => 'パスワードをリセットしました。',
  'sent' => 'パスワードリセットメールを送信しました。',
  'token' => 'このパスワードリセットトークンは無効です。',
  'user' => "メールアドレスに一致するユーザーは存在していません。",
  'throttled' => 'しばらく待ってから再度試してください。',

];
```

# 6-5 パスワード再設定メール(テキスト版)のテンプレート作成

+ `$ mkdir resources/views/emails && touch $_/password_reset.blade.php`を実行<br>

+ `resources/views/emails/password_reset.blade.php`を編集<br>

```html:password_reset.blade.php
下記のURLからパスワードの再設定を行なって下さい。

{{ $url }}

このURLの有効期間は{{ $count }}分です。

このメールに心当たりがない場合は、第三者がメールアドレスの入力を誤った可能性があります。

その場合は、このメールは破棄していただいて結構です。

memo({{ url(config('app.url')) }})
```

# 6-6 パスワード再設定メール(テキスト版)の送信処理の作成

## 1. Mailableクラスを継承したクラスの作成

+ `$ php artisan make:mail BareMail`を実行<br>

+ `server/app/Mail/BareMail.php`を編集<br>

```php:BareMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BareMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this; // 編集
    }
}
```

## 2. 通知クラスの作成

+ `$ php artisan make:notification PasswordResetNotification`を実行<br>

+ `server/app/Nofifications/PasswordResetNotification.php`を編集<br>

```php:PasswordResetNotification.php
<?php

namespace App\Notifications;

use App\Mail\BareMail; // 追加
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token; // 追加
    public $mail; // 追加

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // 編集
    public function __construct(string $token, BareMail $mail)
    {
        $this->token = $token;
        $this->mail = $mail;
    }
    // ここまで

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // 編集
    public function toMail($notifiable)
    {
        return $this->mail
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->to($notifiable->email)
            ->subject('[memo]パスワード再設定')
            ->text('emails.password_reset')
            ->with([
                'url' => route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->email,
                ]),
                'count' => config(
                    'auth.passwords.' .
                        config('auth.defaults.passwords') .
                        '.expire'
                ),
            ]);
    }
    // ここまで

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
```

+ `server/app/Models/User.php`を編集<br>

```php:User.php
<?php

namespace App\Models;

use App\Mail\BareMail; // 追加
use App\Notifications\PasswordResetNotification; // 追加
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    // 追加
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token, new BareMail()));
    }
}
```

# 6-7 パスワード再設定画面の作成

## 6. パスワード再設定画面のBladeの作成

+ `server/resources/views/auth/passwords/reset.blade.php`を編集<br>

```html:reset.blade.php
@extends('app')

@section('tile', 'パスワード再設定')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">新しいパスワードを設定</h2>

                        @include('error_card_list')

                        <div class="card-text">
                            <form action="{{ route('password.update') }}" method="post">
                                @csrf

                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="md-form">
                                    <label for="password">新しいパスワード</label>
                                    <input class="form-control" type="password" id="password" name="password" required>
                                </div>

                                <div class="md-form">
                                    <label for="password_confirmation">新しいパスワード(再入力)</label>
                                    <input class="form-control" type="password" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>

                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">送信</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

# 7-2 VueコンポーネントをBladeに組み込む

## 1. Vue.jsをインストールする

+ `$ npm i -D vue@2.6.11 vue-template-compiler@2.6.11`を実行<br>

+ `$ mkdir resources/js/components && touch $_/ArticleLike.vue`を実行<br>

+ `server/resources/js/components/ArticleLike.vue`を編集<br>

```vue:ArticleLike.vue
<template>
  <div>
    <button type="button" class="btn m-0 p-1 shadow-none">
      <i class="fas fa-heart mr-1" />
    </button>
    10
  </div>
</template>

<script>
</script>
```

+ `server/resources/js/app.js`を編集<br>

```js:app.js
import './bootstrap'
import Vue from 'vue'
import ArticleLike from './components/ArticleLike'

const app = new Vue({
  el: "#app",
  components: {
    ArticleLike,
  }
})
```

※ vue.js コンパイル失敗時 参考: https://qiita.com/masa___i/items/c2b2174c4540a5f0cee6 <br>


+ `server/resources/views/app.blade.php`を編集<br>

```html:app.blade.php
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>
        @yield('title')
    </title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.11/css/mdb.min.css" rel="stylesheet">
</head>

<body>
    <!-- 編集 -->
    <div id="app">
        @yield('content')
    </div>

    <!-- 追加 -->
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- JQuery -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Bootstrap tooltips -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js">
    </script>
    <!-- MDB core JavaScript -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.11/js/mdb.min.js"></script>
</body>

</html>
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
    <!-- 編集 -->
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
            <article-like>
            </article-like>
        </div>
    </div>
    <!-- ここまで -->
</div>
```