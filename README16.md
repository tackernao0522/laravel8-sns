# 10-5 Googleのアカウントでログイン可能にする

## 1. ルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::prefix('login')->name('login.')->group(function () {
  Route::get('/{provider}', [LoginController::class, 'redirectToProvider'])->name('{provider}');
  // 追加
  Route::get('/{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('{provider}.callback');
});
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
  Route::get('/{name}/likes', [UserController::class, 'likes'])->name('likes');
  Route::get('/{name}/followings', [UserController::class, 'followings'])->name('followings');
  Route::get('/{name}/followers', [UserController::class, 'followers'])->name('followers');
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
});
```

## 2. アクションメソッドの追加

+ `server/app/Http/Controllers/Auth/LoginController.php`を編集<br>

```php:LoginController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // 追加
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // 追加
    public function handleProviderCallback(Request $request, string $provider)
    {
        $providerUser = Socialite::driver($provider)->stateless()->user();

        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            $this->guard()->login($user, true);
            return $this->sendLoginResponse($request);
        }

        // $userがnullの場合の処理は次のパートでここに書く予定
    }
}
```

+ [ユーザー詳細情報の取得 - Laravel Socialite公式](https://readouble.com/laravel/6.x/ja/socialite.html#retrieving-user-details) <br>

+ [where - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-where) <br>

+ [first() - Laravel公式](https://readouble.com/laravel/6.x/ja/collections.html#method-first) <br>

# 10-6 未登録ユーザーの場合にユーザー名登録画面を表示する

## 2. LoginControllerのアクションメソッドの編集

+ `server/app/Http/Controllers/Auth/LoginController.php`を編集<br>

```php:LoginController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        $providerUser = Socialite::driver($provider)->stateless()->user();

        $user = User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            $this->guard()->login($user, true);
            return $this->sendLoginResponse($request);
        }

        // 追加
        return redirect()->route('register.{provider}', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $providerUser->token,
        ]);
    }
}
```

## 3. ユーザー名登録画面表示処理用のルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::prefix('login')->name('login.')->group(function () {
  Route::get('/{provider}', [LoginController::class, 'redirectToProvider'])->name('{provider}');
  Route::get('/{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('{provider}.callback');
});
// 追加
Route::prefix('register')->name('register.')->group(function () {
  Route::get('/{provider}', [RegisterController::class, 'showProviderUserRegistrationForm'])->name('{provider}');
});
// ここまで
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
  Route::get('/{name}/likes', [UserController::class, 'likes'])->name('likes');
  Route::get('/{name}/followings', [UserController::class, 'followings'])->name('followings');
  Route::get('/{name}/followers', [UserController::class, 'followers'])->name('followers');
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
});
```

## 4. ユーザー名登録画面表示処理のアクションメソッドの追加

+ `server/app/Http/Controllers/Auth/RegisterController.php`を編集<br>

```php:RegisterController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request; // 追加
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite; // 追加

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    // 追加
    public function showProviderUserRegistrationForm(Request $request, string $provider)
    {
        $token = $request->token;

        $providerUser = Socialite::driver($provider)->userFromToken($token);

        return view('auth.social_register', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $token,
        ]);
    }
}
```

## 5. ユーザー名登録画面のBladeの作成

+ `$ touch resources/views/auth/social_register.blade.php`を実行<br>

+ `server/resources/views/auth/social_register.blade.php`を編集<br>

```html:social_register.blade.php
@extends('app')

@section('title', 'ユーザー登録')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-card">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">ユーザー登録</h2>

                        @include('error_card_list')
                        <div class="cart-text">
                            <form action="" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="md-form">
                                    <label for="name">ユーザー名</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                    <small>英数字3〜16文字(登録後の変更はできません)</small>
                                </div>
                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        value="{{ $email }}" disabled>
                                </div>
                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">ユーザー登録</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

# 10-7 Googleのアカウントでユーザー登録可能にする

## 1. ルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::prefix('login')->name('login.')->group(function () {
  Route::get('/{provider}', [LoginController::class, 'redirectToProvider'])->name('{provider}');
  Route::get('/{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('{provider}.callback');
});
Route::prefix('register')->name('register.')->group(function () {
  Route::get('/{provider}', [RegisterController::class, 'showProviderUserRegistrationForm'])->name('{provider}');
  // 追加
  Route::post('/{provider}', [RegisterController::class, 'registerProviderUser'])->name('{provider}');
});
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
  Route::get('/{name}/likes', [UserController::class, 'likes'])->name('likes');
  Route::get('/{name}/followings', [UserController::class, 'followings'])->name('followings');
  Route::get('/{name}/followers', [UserController::class, 'followers'])->name('followers');
  Route::middleware('auth')->group(function () {
    Route::put('/{name}/follow', [UserController::class, 'follow'])->name('follow');
    Route::delete('/{name}/follow', [UserController::class, 'unfollow'])->name('unfollow');
  });
});
```

## 2. アクションメソッドの追加

+ `server/app/Http/Controllers/Auth/RegisterController.php`を編集<br>

```php:RegisterController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function showProviderUserRegistrationForm(Request $request, string $provider)
    {
        $token = $request->token;

        $providerUser = Socialite::driver($provider)->userFromToken($token);

        return view('auth.social_register', [
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'token' => $token,
        ]);
    }

    public function registerProviderUser(Request $request, string $provider)
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'],
            'token' => ['required', 'string']
        ]);

        $token = $request->token;

        $providerUser = Socialite::driver($provider)->userFromToken($token);

        $user = User::create([
            'name' => $request->name,
            'email' => $providerUser->getEmail(),
            'password' => null,
        ]);

        $this->guard()->login($user, true);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
```

## 3. Bladeの編集

+ `server/resources/views/auth/social_register.blade.php`を編集<br>

```html:social_register.blade.php
@extends('app')

@section('title', 'ユーザー登録')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">ユーザー登録</h2>

                        @include('error_card_list')
                        <div class="cart-text">
                            <form action="{{ route('register.{provider}', ['provider' => $provider]) }}" method="POST"> <!-- 編集 -->
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="md-form">
                                    <label for="name">ユーザー名</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                    <small>英数字3〜16文字(登録後の変更はできません)</small>
                                </div>
                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        value="{{ $email }}" disabled>
                                </div>
                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">ユーザー登録</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

# 10-8 ユーザー登録画面に「Googleで登録」ボタンを表示する

## 2. Bladeの編集

+ `server/resources/views/auth/register.blade.php`を編集<br>

```html:register.blade.php
@extends('app')

@section('title', 'ユーザー登録')

@section('content')
    <div class="container">
        <div class="row">
            <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                <h1 class="text-center"><a href="/" class="text-dark">memo</a></h1>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h2 class="h3 card-title text-center mt-2">ユーザー登録</h2>

                        <!-- 追加 -->
                        <a href="{{ route('login.{provider}', ['provider' => 'google']) }}" class="btn btn-block btn-danger">
                            <i class="fab fa-google mr-1"></i>Googleで登録
                        </a>

                        <div class="card-text">

                            @include('error_card_list')

                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="md-form">
                                    <label for="name">ユーザー名</label>
                                    <input type="text" id="name" name="name" class="form-control" required
                                        value="{{ old('name') }}">
                                    <small>英数字3〜16文字(登録後の変更はできません)</small>
                                </div>
                                <div class="md-form">
                                    <label for="email">メールアドレス</label>
                                    <input type="text" id="email" name="email" class="form-control" required
                                        value="{{ old('email') }}">
                                </div>
                                <div class="md-form">
                                    <label for="password">パスワード</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <div class="md-form">
                                    <label for="password_confirmation">パスワード(確認)</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" required>
                                </div>
                                <button class="btn btn-block blue-gradient mt-2 mb-2" type="submit">ユーザー登録</button>
                            </form>

                            <div class="mt-0">
                                <a href="{{ route('login') }}" class="card-text">ログインはこちら</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```
