<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cardápio online do Foodflix.">
    <title>@yield('title', 'Cardápio') | Foodflix</title>
    <style>
        :root {
            --primary: #f04e23;
            --primary-dark: #da3d17;
            --primary-soft: #fff1eb;
            --cta: #e8271a;
            --cta-dark: #cb2016;
            --text: #1f2937;
            --muted: #6b7280;
            --background: #f7f7f8;
            --surface: #fff;
            --border: #e8e9ed;
            --input: #f1f2f4;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        button, input { font: inherit; }
        a { color: inherit; }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid rgba(31,41,55,.07);
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(12px);
        }

        .header-main {
            width: min(100% - 40px, 1200px);
            min-height: 74px;
            display: flex;
            align-items: center;
            gap: 30px;
            margin: 0 auto;
        }

        .site-logo { display: flex; align-items: center; gap: 9px; color: var(--primary); font-size: 23px; font-weight: 850; letter-spacing: -.6px; text-decoration: none; }
        .logo-mark { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 11px; background: var(--primary); color: white; }

        .category-nav { display: flex; align-items: center; gap: 21px; margin-left: 8px; }
        .category-nav a { color: #4b5563; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .category-nav a:hover { color: var(--primary); }

        .header-tools { display: flex; align-items: center; gap: 12px; margin-left: auto; }
        .search-form { position: relative; width: min(28vw, 285px); }
        .search-form svg { position: absolute; top: 50%; left: 13px; width: 18px; height: 18px; transform: translateY(-50%); color: #8b919b; }
        .search-input { width: 100%; height: 42px; padding: 0 14px 0 40px; border: 1px solid transparent; border-radius: 999px; outline: 0; background: var(--input); color: var(--text); font-size: 13px; }
        .search-input:focus { border-color: rgba(240,78,35,.45); background: white; box-shadow: 0 0 0 3px rgba(240,78,35,.08); }

        .header-icon { width: 41px; height: 41px; position: relative; display: grid; place-items: center; border: 0; border-radius: 50%; background: transparent; color: #4b5563; cursor: pointer; text-decoration: none; }
        .header-icon:hover { background: var(--primary-soft); color: var(--primary); }
        .header-icon svg { width: 21px; height: 21px; }
        .account-wrap { position: relative; }
        .account-menu { min-width: 185px; position: absolute; top: 49px; right: 0; display: none; overflow: hidden; border: 1px solid var(--border); border-radius: 10px; background: #fff; box-shadow: 0 15px 35px rgba(31,41,55,.15); }
        .account-menu.open { display: block; }
        .account-menu a, .account-menu button { width: 100%; min-height: 42px; display: flex; align-items: center; padding: 0 15px; border: 0; background: #fff; color: #4b5563; cursor: pointer; font-size: 12px; font-weight: 700; text-align: left; text-decoration: none; }
        .account-menu a:hover, .account-menu button:hover { background: var(--primary-soft); color: var(--primary); }
        .account-menu form { border-top: 1px solid var(--border); }
        .cart-wrap { position: relative; }
        .cart-count { position: absolute; top: 1px; right: 0; min-width: 17px; height: 17px; padding: 0 4px; display: grid; place-items: center; border: 2px solid white; border-radius: 999px; background: var(--cta); color: white; font-size: 9px; font-weight: 800; }
        .mini-cart { width: 365px; max-height: min(560px, calc(100vh - 100px)); position: absolute; top: 49px; right: 0; display: none; overflow: auto; border: 1px solid var(--border); border-radius: 13px; background: white; box-shadow: 0 20px 50px rgba(31,41,55,.18); }
        .mini-cart.open { display: block; }
        .mini-cart-header { display: flex; justify-content: space-between; padding: 17px 18px; border-bottom: 1px solid var(--border); }
        .mini-cart-header strong { font-size: 14px; }
        .mini-cart-header span { color: var(--muted); font-size: 11px; }
        .mini-cart-items { padding: 4px 15px; }
        .mini-cart-item { display: grid; grid-template-columns: 55px minmax(0, 1fr) auto; gap: 11px; padding: 13px 0; }
        .mini-cart-item + .mini-cart-item { border-top: 1px solid var(--border); }
        .mini-cart-image { width: 55px; height: 51px; display: grid; place-items: center; overflow: hidden; border-radius: 7px; background: #eff0f2; color: #9ea4ad; }
        .mini-cart-image img { width: 100%; height: 100%; object-fit: cover; }
        .mini-cart-copy > strong { display: block; overflow: hidden; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .mini-cart-copy > span { display: block; margin-top: 3px; color: var(--primary); font-size: 11px; font-weight: 750; }
        .mini-quantity { display: flex; align-items: center; gap: 5px; margin-top: 7px; }
        .mini-quantity button { width: 23px; height: 23px; padding: 0; border: 1px solid var(--border); border-radius: 5px; background: white; cursor: pointer; }
        .mini-quantity span { min-width: 18px; text-align: center; font-size: 11px; font-weight: 700; }
        .mini-cart-side { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; }
        .mini-cart-side button { padding: 0 3px; border: 0; background: transparent; color: #a0a5ad; cursor: pointer; font-size: 18px; }
        .mini-cart-side strong { font-size: 11px; }
        .mini-cart-footer { padding: 16px 18px 18px; border-top: 1px solid var(--border); }
        .mini-cart-footer div { display: flex; justify-content: space-between; font-size: 13px; }
        .mini-cart-footer a { min-height: 41px; display: grid; place-items: center; margin-top: 14px; border-radius: 8px; background: var(--cta); color: white; font-size: 12px; font-weight: 800; text-decoration: none; }
        .mini-cart-empty { padding: 34px 20px; text-align: center; }
        .mini-cart-empty p { margin: 0 0 14px; color: var(--muted); font-size: 13px; }
        .mini-cart-empty a { color: var(--primary); font-size: 12px; font-weight: 750; text-decoration: none; }

        .site-main { min-height: calc(100vh - 150px); }
        .container { width: min(100% - 40px, 1200px); margin: 0 auto; }

        .site-footer { margin-top: 60px; padding: 29px 20px; border-top: 1px solid var(--border); background: white; color: var(--muted); text-align: center; font-size: 12px; }

        .cart-notice { position: fixed; right: 24px; bottom: 24px; z-index: 50; max-width: min(360px, calc(100% - 48px)); padding: 13px 17px; border-radius: 9px; background: #1f2937; color: white; box-shadow: 0 13px 30px rgba(0,0,0,.2); font-size: 13px; opacity: 0; transform: translateY(12px); pointer-events: none; transition: opacity .2s, transform .2s; }
        .cart-notice.visible { opacity: 1; transform: translateY(0); }

        @media (max-width: 980px) {
            .category-nav { display: none; }
            .search-form { width: min(42vw, 320px); }
        }

        @media (max-width: 640px) {
            .header-main { width: min(100% - 24px, 1200px); min-height: 68px; gap: 10px; }
            .site-logo { font-size: 20px; }
            .logo-mark { width: 35px; height: 35px; }
            .search-form { position: absolute; top: 68px; left: 0; width: 100%; padding: 9px 16px; border-bottom: 1px solid var(--border); background: white; }
            .search-form svg { left: 29px; }
            .site-main { padding-top: 60px; }
            .container { width: min(100% - 28px, 1200px); }
            .mini-cart { position: fixed; top: 69px; right: 12px; width: calc(100% - 24px); max-height: calc(100vh - 90px); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <a class="site-logo" href="{{ route('site.home') }}">
                <span class="logo-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h18"></path><path d="M5 11a7 7 0 0 1 14 0"></path><path d="M12 4V2"></path><path d="M4 16h16"></path><path d="M6 16v4h12v-4"></path></svg>
                </span>
                Foodflix
            </a>

            <nav class="category-nav" aria-label="Categorias">
                @foreach (($navigationCategories ?? collect()) as $navigationCategory)
                    <a href="{{ route('site.home').'#categoria-'.$navigationCategory->slug }}">{{ $navigationCategory->name }}</a>
                @endforeach
            </nav>

            <div class="header-tools">
                <form class="search-form" method="GET" action="{{ route('site.home') }}" role="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                    <input class="search-input" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="Buscar por lanche, pizza..." aria-label="Buscar produtos">
                </form>
                @auth('web')
                    <div class="account-wrap">
                        <button class="header-icon account" type="button" data-account-toggle aria-label="Abrir menu da conta" aria-expanded="false" title="Conta de {{ auth('web')->user()->name }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                        </button>
                        <div class="account-menu" data-account-menu>
                            <a href="{{ route('site.orders.index') }}">Meus Pedidos</a>
                            <form method="POST" action="{{ route('site.logout') }}">@csrf<button type="submit">Sair da conta</button></form>
                        </div>
                    </div>
                @else
                    <a class="header-icon account" href="{{ route('site.login') }}" aria-label="Acessar conta" title="Acessar sua conta">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                    </a>
                @endauth
                <div class="cart-wrap">
                    <button class="header-icon" type="button" data-cart-toggle aria-label="Abrir carrinho de compras" aria-expanded="{{ session('cart_open') ? 'true' : 'false' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3h2l2.2 11h10.7l2-7H6"></path><circle cx="9" cy="19" r="1.5"></circle><circle cx="18" cy="19" r="1.5"></circle></svg>
                        <span class="cart-count">{{ $cartSummary['item_count'] }}</span>
                    </button>
                    @include('site.cart._mini')
                </div>
            </div>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">&copy; {{ date('Y') }} Foodflix. Sabor e praticidade em cada pedido.</footer>
    <div class="cart-notice {{ session('cart_message') ? 'visible' : '' }}" role="status" aria-live="polite">{{ session('cart_message', 'O checkout será implementado na próxima etapa.') }}</div>

    <script>
        (function () {
            var notice = document.querySelector('.cart-notice');
            var toggle = document.querySelector('[data-cart-toggle]');
            var miniCart = document.querySelector('[data-mini-cart]');
            var accountToggle = document.querySelector('[data-account-toggle]');
            var accountMenu = document.querySelector('[data-account-menu]');
            var timer;

            if (toggle && miniCart) {
                toggle.addEventListener('click', function () {
                    var open = miniCart.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.cart-wrap')) {
                        miniCart.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            if (accountToggle && accountMenu) {
                accountToggle.addEventListener('click', function () {
                    var open = accountMenu.classList.toggle('open');
                    accountToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.account-wrap')) {
                        accountMenu.classList.remove('open');
                        accountToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            document.querySelectorAll('[data-checkout-preview]').forEach(function (button) {
                button.addEventListener('click', function () {
                    clearTimeout(timer);
                    notice.textContent = 'O checkout será implementado na próxima etapa.';
                    notice.classList.add('visible');
                    timer = setTimeout(function () { notice.classList.remove('visible'); }, 2800);
                });
            });

            if (notice.classList.contains('visible')) {
                timer = setTimeout(function () { notice.classList.remove('visible'); }, 2800);
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
