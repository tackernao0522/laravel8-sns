# 1-5 記事テーブルとユーザーテーブルの作成

## 1. データベースの作成

## 3. 記事テーブルの作成

+ `php artisan make:migration create_articles_table --create=articles`を実行<br>

+ `server/database/migrations/create_articles_table.php`を編集<br>

```php:create_articles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('articles');
    }
}
```

## 4. usersテーブルのマイグレションファイルの編集

+ `server/database/migrations/create_users_table.php`を編集<br>

```php:create_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 編集
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // 編集
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
```

## 5. マイグレーションの実行

+ `$ php artisan migrate`を実行<br>

# 1-6 記事モデルの作成

## 1. 記事モデルの作成

+ `$ php artisan make:model Article`を実行<br>

## 2. リレーションの追加

+ `server/app/Models/Article.php`を編集<br>

```php:Article.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo('App/Models/User');
    }
}
```

# 1-7 記事モデルから記事情報を取得する

## 1. コントローラの編集<br>

+ `server/app/Http/Controllers/ArticleController.php`を編集<br>

```php:ArticleController.php
<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all()->sortByDesc('created_at');

        return view('articles.index', compact('articles'));
    }
}
```

# 2-2 ルーティングの追加

## 1. 認証関連のルーティングの追加

+ `server/routes/web.php`を編集<br>

```php:web.php
<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(); // 追加
Route::get('/', [ArticleController::class, 'index']);
```

# 2-3 コントローラの確認とリダイレクト先の変更

## 4. registerアクションメソッドの確認とredirectToプロパティの変更

+ `server/app/Providers/RouteServiceProvider.php`を編集<br>

```php:RouteServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/'; // 編集

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
```

# 2-4 コントローラの確認とバリデーションの変更

+ `server/app/Http/Controllers/Auth/RegisterController.php`を編集<br>

```php:RegisterController.php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            'name' => ['required', 'string', 'alpha_num', 'min:3', 'max:16', 'unique:users'], // 編集
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
}
```

# 2-5 ユーザー登録画面の作成

+ `server/resources/views/auth/register.blade.php`を編集<br>

```php:register.blade.php
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

                        <div class="card-text">
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="md-form">
                                    <label for="name">ユーザー名</label>
                                    <input type="text" id="name" name="name" class="form-control" required
                                        value="{{ old('name') }}">
                                    <small>英数字3〜6文字(登録後の変更はできません)</small>
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

# 2-6 ログアウトを可能にする

## 3. ナビバーの編集

+ `server/resources/views/nav.blade.php`を編集<br>

```php:nav.blade.php
<nav class="navbar navbar-expand navbar-dark blue-gradient">

    <a href="/" class="navbar-brand"><i class="far fa-sticky-not mr-1"></i>memo</a>

    <ul class="navbar-nav ml-auto">

        @guest // 追加
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">ユーザー登録</a> // 編集
            </li>
        @endguest // 追加

        @guest // 追加
            <li class="nav-item">
                <a href="" class="nav-link">ログイン</a>
            </li>
        @endguest // 追加

        @auth // 追加
            <li class="nav-item">
                <a href="" class="nav-link"><i class="fas fa-pen mr-1"></i>投稿する</a>
            </li>
        @endauth // 追加

        @auth // 追加
            {{-- Dropdown --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-primary" aria-labelledby="navbarDropdownMenuLink">
                    <button class="dropdown-item type="button" onclick="location.href="">
                        マイページ
                    </button>
                    <div class="dropdown-divider"></div>
                    <button form="logout-button" class="dropdown-item" type="submit">
                        ログアウト
                    </button>
                </div>
            </li>
            <form action="{{ route('logout') }}" id="logout-button" method="POST"> // 編集
                @csrf
            </form>
            {{-- Dropdown --}}
        @endauth // 追加
    </ul>
</nav>
```

# 2-7 ユーザー登録時のエラーメッセージを表示する

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

                        <div class="card-text">

                            @include('error_card_list') // 追記

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

+ `$ touch server/resources/views/error_card_list.blade.php`を実行<br>

+ `server/resoources/views/error_card_list.blade.php`を編集<br>

```html:error_card_list.blade.php
@if ($errors->any())
    <div class="card-text text-left alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

## 5. バリデーションエラーメッセージの日本語化

+ `mkdir resources/lang/ja && touch $_/validation.php`を実行<br>

+ `server/resources/lang/ja/validation.php`を編集<br>

```php:validation.php
<?php

return [

  /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

  'accepted'             => ':attributeを承認してください。',
  'active_url'           => ':attributeが有効なURLではありません。',
  'after'                => ':attributeには、:dateより後の日付を指定してください。',
  'after_or_equal'       => ':attributeには、:date以降の日付を指定してください。',
  'alpha'                => ':attributeはアルファベットのみがご利用できます。',
  'alpha_dash'           => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
  'alpha_num'            => ':attributeはアルファベット数字がご利用できます。',
  'array'                => ':attributeは配列でなくてはなりません。',
  'before'               => ':attributeには、:dateより前の日付をご利用ください。',
  'before_or_equal'      => ':attributeには、:date以前の日付をご利用ください。',
  'between'              => [
    'numeric' => ':attributeは、:minから:maxの間で指定してください。',
    'file'    => ':attributeは、:min kBから、:max kBの間で指定してください。',
    'string'  => ':attributeは、:min文字から、:max文字の間で指定してください。',
    'array'   => ':attributeは、:min個から:max個の間で指定してください。',
  ],
  'boolean'              => ':attributeは、trueかfalseを指定してください。',
  'confirmed'            => ':attributeと、確認フィールドとが、一致していません。',
  'date'                 => ':attributeには有効な日付を指定してください。',
  'date_equals'          => ':attributeには、:dateと同じ日付けを指定してください。',
  'date_format'          => ':attributeは:format形式で指定してください。',
  'different'            => ':attributeと:otherには、異なった内容を指定してください。',
  'digits'               => ':attributeは:digits桁で指定してください。',
  'digits_between'       => ':attributeは:min桁から:max桁の間で指定してください。',
  'dimensions'           => ':attributeの図形サイズが正しくありません。',
  'distinct'             => ':attributeには異なった値を指定してください。',
  'email'                => ':attributeには、有効なメールアドレスを指定してください。',
  'ends_with'            => ':attributeには、:valuesのどれかで終わる値を指定してください。',
  'exists'               => '選択された:attributeは正しくありません。',
  'file'                 => ':attributeにはファイルを指定してください。',
  'filled'               => ':attributeに値を指定してください。',
  'gt'                   => [
    'numeric' => ':attributeには、:valueより大きな値を指定してください。',
    'file'    => ':attributeには、:value kBより大きなファイルを指定してください。',
    'string'  => ':attributeは、:value文字より長く指定してください。',
    'array'   => ':attributeには、:value個より多くのアイテムを指定してください。',
  ],
  'gte'                  => [
    'numeric' => ':attributeには、:value以上の値を指定してください。',
    'file'    => ':attributeには、:value kB以上のファイルを指定してください。',
    'string'  => ':attributeは、:value文字以上で指定してください。',
    'array'   => ':attributeには、:value個以上のアイテムを指定してください。',
  ],
  'image'                => ':attributeには画像ファイルを指定してください。',
  'in'                   => '選択された:attributeは正しくありません。',
  'in_array'             => ':attributeには:otherの値を指定してください。',
  'integer'              => ':attributeは整数で指定してください。',
  'ip'                   => ':attributeには、有効なIPアドレスを指定してください。',
  'ipv4'                 => ':attributeには、有効なIPv4アドレスを指定してください。',
  'ipv6'                 => ':attributeには、有効なIPv6アドレスを指定してください。',
  'json'                 => ':attributeには、有効なJSON文字列を指定してください。',
  'lt'                   => [
    'numeric' => ':attributeには、:valueより小さな値を指定してください。',
    'file'    => ':attributeには、:value kBより小さなファイルを指定してください。',
    'string'  => ':attributeは、:value文字より短く指定してください。',
    'array'   => ':attributeには、:value個より少ないアイテムを指定してください。',
  ],
  'lte'                  => [
    'numeric' => ':attributeには、:value以下の値を指定してください。',
    'file'    => ':attributeには、:value kB以下のファイルを指定してください。',
    'string'  => ':attributeは、:value文字以下で指定してください。',
    'array'   => ':attributeには、:value個以下のアイテムを指定してください。',
  ],
  'max'                  => [
    'numeric' => ':attributeには、:max以下の数字を指定してください。',
    'file'    => ':attributeには、:max kB以下のファイルを指定してください。',
    'string'  => ':attributeは、:max文字以下で指定してください。',
    'array'   => ':attributeは:max個以下指定してください。',
  ],
  'mimes'                => ':attributeには:valuesタイプのファイルを指定してください。',
  'mimetypes'            => ':attributeには:valuesタイプのファイルを指定してください。',
  'min'                  => [
    'numeric' => ':attributeには、:min以上の数字を指定してください。',
    'file'    => ':attributeには、:min kB以上のファイルを指定してください。',
    'string'  => ':attributeは、:min文字以上で指定してください。',
    'array'   => ':attributeは:min個以上指定してください。',
  ],
  'not_in'               => '選択された:attributeは正しくありません。',
  'not_regex'            => ':attributeの形式が正しくありません。',
  'numeric'              => ':attributeには、数字を指定してください。',
  'present'              => ':attributeが存在していません。',
  'regex'                => ':attributeに正しい形式を指定してください。',
  'required'             => ':attributeは必ず指定してください。',
  'required_if'          => ':otherが:valueの場合、:attributeも指定してください。',
  'required_unless'      => ':otherが:valuesでない場合、:attributeを指定してください。',
  'required_with'        => ':valuesを指定する場合は、:attributeも指定してください。',
  'required_with_all'    => ':valuesを指定する場合は、:attributeも指定してください。',
  'required_without'     => ':valuesを指定しない場合は、:attributeを指定してください。',
  'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
  'same'                 => ':attributeと:otherには同じ値を指定してください。',
  'size'                 => [
    'numeric' => ':attributeは:sizeを指定してください。',
    'file'    => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
    'string'  => ':attributeは:size文字で指定してください。',
    'array'   => ':attributeは:size個指定してください。',
  ],
  'starts_with'          => ':attributeには、:valuesのどれかで始まる値を指定してください。',
  'string'               => ':attributeは文字列を指定してください。',
  'timezone'             => ':attributeには、有効なゾーンを指定してください。',
  'unique'               => ':attributeの値は既に存在しています。',
  'uploaded'             => ':attributeのアップロードに失敗しました。',
  'url'                  => ':attributeに正しい形式を指定してください。',
  'uuid'                 => ':attributeに有効なUUIDを指定してください。',

  /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

  'custom' => [
    '属性名' => [
      'ルール名' => 'カスタムメッセージ',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

  'attributes' => [],

];
```

+ `server/config/app.php`を編集<br>

```php:app.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Tokyo',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ja', // 編集

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Js' => Illuminate\Support\Js::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'RateLimiter' => Illuminate\Support\Facades\RateLimiter::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

];
```

## 7. 項目名の日本語化

+ `server/config/app.php`を編集<br>

```php:app.php
<?php

return [

  /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

  'accepted'             => ':attributeを承認してください。',
  'active_url'           => ':attributeが有効なURLではありません。',
  'after'                => ':attributeには、:dateより後の日付を指定してください。',
  'after_or_equal'       => ':attributeには、:date以降の日付を指定してください。',
  'alpha'                => ':attributeはアルファベットのみがご利用できます。',
  'alpha_dash'           => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
  'alpha_num'            => ':attributeはアルファベット数字がご利用できます。',
  'array'                => ':attributeは配列でなくてはなりません。',
  'before'               => ':attributeには、:dateより前の日付をご利用ください。',
  'before_or_equal'      => ':attributeには、:date以前の日付をご利用ください。',
  'between'              => [
    'numeric' => ':attributeは、:minから:maxの間で指定してください。',
    'file'    => ':attributeは、:min kBから、:max kBの間で指定してください。',
    'string'  => ':attributeは、:min文字から、:max文字の間で指定してください。',
    'array'   => ':attributeは、:min個から:max個の間で指定してください。',
  ],
  'boolean'              => ':attributeは、trueかfalseを指定してください。',
  'confirmed'            => ':attributeと、確認フィールドとが、一致していません。',
  'date'                 => ':attributeには有効な日付を指定してください。',
  'date_equals'          => ':attributeには、:dateと同じ日付けを指定してください。',
  'date_format'          => ':attributeは:format形式で指定してください。',
  'different'            => ':attributeと:otherには、異なった内容を指定してください。',
  'digits'               => ':attributeは:digits桁で指定してください。',
  'digits_between'       => ':attributeは:min桁から:max桁の間で指定してください。',
  'dimensions'           => ':attributeの図形サイズが正しくありません。',
  'distinct'             => ':attributeには異なった値を指定してください。',
  'email'                => ':attributeには、有効なメールアドレスを指定してください。',
  'ends_with'            => ':attributeには、:valuesのどれかで終わる値を指定してください。',
  'exists'               => '選択された:attributeは正しくありません。',
  'file'                 => ':attributeにはファイルを指定してください。',
  'filled'               => ':attributeに値を指定してください。',
  'gt'                   => [
    'numeric' => ':attributeには、:valueより大きな値を指定してください。',
    'file'    => ':attributeには、:value kBより大きなファイルを指定してください。',
    'string'  => ':attributeは、:value文字より長く指定してください。',
    'array'   => ':attributeには、:value個より多くのアイテムを指定してください。',
  ],
  'gte'                  => [
    'numeric' => ':attributeには、:value以上の値を指定してください。',
    'file'    => ':attributeには、:value kB以上のファイルを指定してください。',
    'string'  => ':attributeは、:value文字以上で指定してください。',
    'array'   => ':attributeには、:value個以上のアイテムを指定してください。',
  ],
  'image'                => ':attributeには画像ファイルを指定してください。',
  'in'                   => '選択された:attributeは正しくありません。',
  'in_array'             => ':attributeには:otherの値を指定してください。',
  'integer'              => ':attributeは整数で指定してください。',
  'ip'                   => ':attributeには、有効なIPアドレスを指定してください。',
  'ipv4'                 => ':attributeには、有効なIPv4アドレスを指定してください。',
  'ipv6'                 => ':attributeには、有効なIPv6アドレスを指定してください。',
  'json'                 => ':attributeには、有効なJSON文字列を指定してください。',
  'lt'                   => [
    'numeric' => ':attributeには、:valueより小さな値を指定してください。',
    'file'    => ':attributeには、:value kBより小さなファイルを指定してください。',
    'string'  => ':attributeは、:value文字より短く指定してください。',
    'array'   => ':attributeには、:value個より少ないアイテムを指定してください。',
  ],
  'lte'                  => [
    'numeric' => ':attributeには、:value以下の値を指定してください。',
    'file'    => ':attributeには、:value kB以下のファイルを指定してください。',
    'string'  => ':attributeは、:value文字以下で指定してください。',
    'array'   => ':attributeには、:value個以下のアイテムを指定してください。',
  ],
  'max'                  => [
    'numeric' => ':attributeには、:max以下の数字を指定してください。',
    'file'    => ':attributeには、:max kB以下のファイルを指定してください。',
    'string'  => ':attributeは、:max文字以下で指定してください。',
    'array'   => ':attributeは:max個以下指定してください。',
  ],
  'mimes'                => ':attributeには:valuesタイプのファイルを指定してください。',
  'mimetypes'            => ':attributeには:valuesタイプのファイルを指定してください。',
  'min'                  => [
    'numeric' => ':attributeには、:min以上の数字を指定してください。',
    'file'    => ':attributeには、:min kB以上のファイルを指定してください。',
    'string'  => ':attributeは、:min文字以上で指定してください。',
    'array'   => ':attributeは:min個以上指定してください。',
  ],
  'not_in'               => '選択された:attributeは正しくありません。',
  'not_regex'            => ':attributeの形式が正しくありません。',
  'numeric'              => ':attributeには、数字を指定してください。',
  'present'              => ':attributeが存在していません。',
  'regex'                => ':attributeに正しい形式を指定してください。',
  'required'             => ':attributeは必ず指定してください。',
  'required_if'          => ':otherが:valueの場合、:attributeも指定してください。',
  'required_unless'      => ':otherが:valuesでない場合、:attributeを指定してください。',
  'required_with'        => ':valuesを指定する場合は、:attributeも指定してください。',
  'required_with_all'    => ':valuesを指定する場合は、:attributeも指定してください。',
  'required_without'     => ':valuesを指定しない場合は、:attributeを指定してください。',
  'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
  'same'                 => ':attributeと:otherには同じ値を指定してください。',
  'size'                 => [
    'numeric' => ':attributeは:sizeを指定してください。',
    'file'    => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
    'string'  => ':attributeは:size文字で指定してください。',
    'array'   => ':attributeは:size個指定してください。',
  ],
  'starts_with'          => ':attributeには、:valuesのどれかで始まる値を指定してください。',
  'string'               => ':attributeは文字列を指定してください。',
  'timezone'             => ':attributeには、有効なゾーンを指定してください。',
  'unique'               => ':attributeの値は既に存在しています。',
  'uploaded'             => ':attributeのアップロードに失敗しました。',
  'url'                  => ':attributeに正しい形式を指定してください。',
  'uuid'                 => ':attributeに有効なUUIDを指定してください。',

  /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

  'custom' => [
    '属性名' => [
      'ルール名' => 'カスタムメッセージ',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

  'attributes' => [
    'name' => 'ユーザー名', // 追加
    'email' => 'メールアドレス', // 追加
    'password' => 'パスワード' // 追加
  ],

];
```