<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel Administrativo') | Foodflix</title>
    <style>
        :root {
            --primary: #f04e23;
            --primary-soft: #fff1eb;
            --cta: #e8271a;
            --text: #1f2937;
            --muted: #6b7280;
            --background: #f5f6f8;
            --surface: #fff;
            --border: #e8e9ed;
            --input: #f0f1f3;
            --success: #16a34a;
            --danger: #dc2626;
            --sidebar: 244px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--background);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        button, input, select, textarea { font: inherit; }
        a { color: inherit; }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 20;
            width: var(--sidebar);
            display: flex;
            flex-direction: column;
            padding: 27px 18px 20px;
            border-right: 1px solid var(--border);
            background: var(--surface);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 10px 29px;
            color: var(--primary);
            text-decoration: none;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            display: grid;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 11px;
            background: var(--primary);
            color: white;
        }

        .brand strong { font-size: 21px; letter-spacing: -.5px; }
        .brand small { display: block; margin-top: 1px; color: var(--muted); font-size: 10px; font-weight: 500; }

        .nav { display: grid; gap: 7px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 46px;
            padding: 0 13px;
            border-radius: 9px;
            color: #5d6571;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .nav-link svg { width: 20px; height: 20px; }
        .nav-link:hover { background: #f7f7f8; color: var(--text); }
        .nav-link.active { background: var(--primary-soft); color: var(--primary); }

        .logout-form { margin-top: auto; }
        .logout-button { width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left; }

        .page-shell { min-height: 100vh; margin-left: var(--sidebar); }

        .topbar {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 34px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.9);
        }

        .admin-identity { text-align: right; }
        .admin-identity strong { display: block; font-size: 13px; }
        .admin-identity span { color: var(--muted); font-size: 11px; }

        .page-content { padding: 32px 36px 48px; }
        .page-content-inner { width: min(100%, 1220px); margin: 0 auto; }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-heading h1 { margin: 0 0 5px; font-size: 25px; letter-spacing: -.5px; }
        .page-heading p { margin: 0; color: var(--muted); font-size: 14px; }

        .button {
            min-height: 43px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 17px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .button svg { width: 17px; height: 17px; }
        .button-primary { background: var(--cta); color: white; box-shadow: 0 6px 14px rgba(232,39,26,.16); }
        .button-primary:hover { background: #cf2015; }
        .button-secondary { border: 1px solid var(--border); background: white; color: var(--text); }

        .card { overflow: hidden; border: 1px solid var(--border); border-radius: 12px; background: var(--surface); box-shadow: 0 6px 22px rgba(31,41,55,.04); }
        .alert { margin-bottom: 20px; padding: 13px 16px; border-radius: 9px; font-size: 13px; }
        .alert-success { border: 1px solid #bbf7d0; background: #f0fdf4; color: #15803d; }
        .alert-error { border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; }

        @media (max-width: 780px) {
            :root { --sidebar: 82px; }
            .sidebar { padding-inline: 13px; }
            .brand { justify-content: center; padding-inline: 0; }
            .brand-text, .nav-label { display: none; }
            .nav-link { justify-content: center; padding: 0; }
            .topbar { padding-inline: 20px; }
            .page-content { padding: 24px 18px 40px; }
            .page-heading { align-items: flex-start; flex-direction: column; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11h18"></path><path d="M5 11a7 7 0 0 1 14 0"></path><path d="M12 4V2"></path><path d="M4 16h16"></path><path d="M6 16v4h12v-4"></path>
                </svg>
            </span>
            <span class="brand-text"><strong>Foodflix</strong><small>Painel Administrativo</small></span>
        </a>

        <nav class="nav" aria-label="Navegação administrativa">
            <a class="nav-link {{ request()->routeIs('admin.dashboard', 'admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>
                <span class="nav-label">Pedidos</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                <span class="nav-label">Produtos</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.97 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.52-1H3v-4h.08A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88L4.2 7.06l2.83-2.83.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.08V3h4v.08a1.7 1.7 0 0 0 1.03 1.52 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.24.62.83 1.02 1.5 1.02H21v4h-.08A1.7 1.7 0 0 0 19.4 15Z"></path></svg>
                <span class="nav-label">Configurações</span>
            </a>
        </nav>

        <form class="logout-form" method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="nav-link logout-button" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path></svg>
                <span class="nav-label">Sair</span>
            </button>
        </form>
    </aside>

    <div class="page-shell">
        <header class="topbar">
            <div class="admin-identity">
                <strong>{{ auth('admin')->user()->name }}</strong>
                <span>Administrador</span>
            </div>
        </header>

        <main class="page-content">
            <div class="page-content-inner">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
