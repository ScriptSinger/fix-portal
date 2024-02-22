<header class="market-header header">
    <div class="container-fluid">
        <nav class="navbar navbar-toggleable-md navbar-inverse fixed-top bg-inverse">
            <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="{{ route('articles.index') }}"><img
                    src="{{ optional($customization)->getImage('logo') ?? asset('assets/front/images/version/market-logo.png') }}"
                    alt=""></a>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('articles.index') }}">Статьи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('questions.index') }}">Вопросы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('firmwares.index') }}">Прошивки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">Мастера</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="marketing-contact.html">О Нас</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a href="{{ route('profile.edit') }}" class="nav-link">{{ auth()->user()->name }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Выход
                            </a>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                            @method('POST')
                        </form>
                    @endauth
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Регистрация</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link">Вход</a>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>
    </div><!-- end container-fluid -->
</header><!-- end market-header -->
