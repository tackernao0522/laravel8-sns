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
