<nav class="navbar navbar-expand navbar-dark blue-gradient">

    <a href="/" class="navbar-brand"><i class="far fa-sticky-not mr-1"></i>memo</a>

    <ul class="navbar-nav ml-auto">

        @guest
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">ユーザー登録</a>
            </li>
        @endguest

        @guest
            <li class="nav-item">
                <a href="" class="nav-link">ログイン</a>
            </li>
        @endguest

        @auth
            <li class="nav-item">
                <a href="" class="nav-link"><i class="fas fa-pen mr-1"></i>投稿する</a>
            </li>
        @endauth

        @auth
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
            <form action="{{ route('logout') }}" id="logout-button" method="POST">
                @csrf
            </form>
            {{-- Dropdown --}}
        @endauth
    </ul>
</nav>
