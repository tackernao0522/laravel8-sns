# 9-12 Bladeの共通化

## 1. show.blade.phpの編集

+ `server/resources/views/users/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $user->name)

@section('content')
    @include('nav')
    <div class="container">
        @include('users/user')
        <ul class="nav nav-tabs nav-justified mt-3">
            <li class="nav-item">
                <a href="{{ route('users.show', $user->name) }}" class="nav-link text-muted active">
                    記事
                </a>
            </li>
            <li class="nav-item">
                <a href="" class="nav-link text-muted">
                    いいね
                </a>
            </li>
        </ul>
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

## 2. タブ部分の共通化

+ `$ touch resources/views/users/tabs.blade.php`を実行<br>

+ `server/resources/views/users/tabs.blade.php`を編集<br>

```html:tabs.blade.php
<ul class="nav nav-tabs nav-justified mt-3">
    <li class="nav-item">
        <a href="{{ route('users.show', $user->name) }}" class="nav-link text-muted {{ $hasArticles ? 'active' : '' }}">
            記事
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('users.likes', $user->name) }}" class="nav-link text-muted {{ $hasLikes ? 'active' : '' }}">
            いいね
        </a>
    </li>
</ul>
```

+ [参考演算子 - PHP公式マニュアル](https://www.php.net/manual/ja/language.operators.comparison.php#language.operators.comparison.ternary) <br>

## 3. show.blade.phpでtabs.blade.phpを使用する

+ `server/resources/views/users/show.blade.php`を編集<br>

```html:show.blade.php
@extends('app')

@section('title', $user->name)

@section('content')
    @include('nav')
    <div class="container">
        @include('users/user')
        @include('users.tabs', ['hasArticles' => true, 'hasLikes' => false])
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```

+ [サブビューの読み込み - Laravel公式](https://readouble.com/laravel/6.x/ja/blade.html#including-subviews) <br>

## 4. likes.blade.phpでtabs.blade.phpを使用する

+ `server/resources/views/users/likes.blade.php`を編集<br>

```html:likes.blade.php
@extends('app')

@section('title', $user->name . 'のいいねした記事')

@section('content')
    @include('nav')
    <div class="container">
        @include('users.user')
        @include('users.tabs', ['hasArticles' => false, 'hasLikes' => true])
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
```