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
        .cart-count { position: absolute; top: 1px; right: 0; min-width: 17px; height: 17px; padding: 0 4px; display: grid; place-items: center; border: 2px solid white; border-radius: 999px; background: var(--cta); color: white; font-size: 9px; font-weight: 800; }

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
            .header-icon.account { display: none; }
            .container { width: min(100% - 28px, 1200px); }
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
                @foreach ($navigationCategories as $navigationCategory)
                    <a href="{{ route('site.home').'#categoria-'.$navigationCategory->slug }}">{{ $navigationCategory->name }}</a>
                @endforeach
            </nav>

            <div class="header-tools">
                <form class="search-form" method="GET" action="{{ route('site.home') }}" role="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                    <input class="search-input" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="Buscar por lanche, pizza..." aria-label="Buscar produtos">
                </form>
                <button class="header-icon account" type="button" aria-label="Conta do cliente" title="Login do cliente será implementado em uma próxima etapa">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                </button>
                <button class="header-icon" type="button" data-cart-preview aria-label="Carrinho de compras" title="Carrinho será implementado na próxima etapa">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3h2l2.2 11h10.7l2-7H6"></path><circle cx="9" cy="19" r="1.5"></circle><circle cx="18" cy="19" r="1.5"></circle></svg>
                    <span class="cart-count">0</span>
                </button>
            </div>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">&copy; {{ date('Y') }} Foodflix. Sabor e praticidade em cada pedido.</footer>
    <div class="cart-notice" role="status" aria-live="polite">O carrinho será habilitado na próxima etapa.</div>

    <script>
        (function () {
            var notice = document.querySelector('.cart-notice');
            var timer;
            document.querySelectorAll('[data-cart-preview]').forEach(function (button) {
                button.addEventListener('click', function () {
                    clearTimeout(timer);
                    notice.classList.add('visible');
                    timer = setTimeout(function () { notice.classList.remove('visible'); }, 2800);
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
