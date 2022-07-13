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