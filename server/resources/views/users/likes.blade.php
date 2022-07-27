@extends('app')

@section('title', $user->name . 'のいいねした記事')

@section('content')
    @include('nav')
    <div class="container">
        @include('users.user')
        <ul class="nav nav-tabs nav-justified mt-3">
            <li class="nav-item">
                <a href="{{ route('users.show', $user->name) }}" class="nav-link text-muted">
                    記事
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users.likes', $user->name) }}" class="nav-link text-muted active">
                    いいね
                </a>
            </li>
        </ul>
        @foreach ($articles as $article)
            @include('articles.card')
        @endforeach
    </div>
@endsection
