<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Painel de Controle | Foodflix</title>
    <style>
        :root {
            --primary: #f04e23;
            --primary-cta: #e8271a;
            --primary-cta-hover: #cf2015;
            --text: #1f2937;
            --muted: #6b7280;
            --background: #f5f6f8;
            --surface: #ffffff;
            --input: #f0f1f3;
            --danger: #b91c1c;
            --danger-bg: #fef2f2;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px 20px;
            background:
                radial-gradient(circle at 50% 0%, rgba(240, 78, 35, .08), transparent 34%),
                var(--background);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-shell { width: min(100%, 430px); }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            margin-bottom: 24px;
            color: var(--primary);
        }

        .brand-mark {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--primary);
            color: white;
            box-shadow: 0 8px 20px rgba(240, 78, 35, .24);
        }

        .brand-name {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .login-card {
            padding: 35px 34px 32px;
            border: 1px solid rgba(31, 41, 55, .05);
            border-radius: 15px;
            background: var(--surface);
            box-shadow: 0 18px 48px rgba(31, 41, 55, .09);
        }

        .login-card h1 {
            margin: 0;
            text-align: center;
            font-size: 25px;
            line-height: 1.25;
            letter-spacing: -.4px;
        }

        .subtitle {
            margin: 9px 0 28px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }

        .field { margin-bottom: 19px; }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 650;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            width: 18px;
            height: 18px;
            transform: translateY(-50%);
            color: #89909b;
            pointer-events: none;
        }

        input {
            width: 100%;
            height: 49px;
            padding: 0 15px 0 44px;
            border: 1px solid transparent;
            border-radius: 9px;
            outline: none;
            background: var(--input);
            color: var(--text);
            font: inherit;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        input::placeholder { color: #9ca3af; }

        input:focus {
            border-color: rgba(240, 78, 35, .55);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(240, 78, 35, .1);
        }

        input[aria-invalid="true"] { border-color: var(--danger); }

        .field-error {
            margin: 7px 2px 0;
            color: var(--danger);
            font-size: 12px;
        }

        .alert {
            margin: -7px 0 20px;
            padding: 11px 13px;
            border-radius: 8px;
            background: var(--danger-bg);
            color: var(--danger);
            font-size: 13px;
        }

        .success { margin: -7px 0 20px; padding: 11px 13px; border-radius: 8px; background: #f0fdf4; color: #166534; font-size: 13px; }

        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -5px 0 23px;
        }

        .forgot-link {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover { text-decoration: underline; }

        .submit-button {
            width: 100%;
            height: 49px;
            border: 0;
            border-radius: 9px;
            background: var(--primary-cta);
            color: white;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 750;
            box-shadow: 0 8px 18px rgba(232, 39, 26, .2);
            transition: background .2s, transform .1s;
        }

        .submit-button:hover { background: var(--primary-cta-hover); }
        .submit-button:active { transform: translateY(1px); }

        .access-note {
            margin: 21px 0 0;
            text-align: center;
            color: #8b919b;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            body { padding: 24px 16px; }
            .login-card { padding: 29px 22px 26px; }
            .brand-name { font-size: 25px; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <div class="brand" aria-label="Foodflix">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11h18"></path>
                    <path d="M5 11a7 7 0 0 1 14 0"></path>
                    <path d="M12 4V2"></path>
                    <path d="M4 16h16"></path>
                    <path d="M6 16v4h12v-4"></path>
                </svg>
            </span>
            <p class="brand-name">Foodflix</p>
        </div>

        <section class="login-card" aria-labelledby="login-title">
            <h1 id="login-title">Painel de Controle</h1>
            <p class="subtitle">Acesse sua conta para gerenciar a loja</p>

            @if (session('status')) <div class="success" role="status">{{ session('status') }}</div> @endif

            @if ($errors->has('email') && old('email') && $errors->first('email') === 'As credenciais informadas são inválidas.')
                <div class="alert" id="email-error" role="alert">{{ $errors->first('email') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="m3 7 9 6 9-6"></path>
                        </svg>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="seu@email.com"
                            autocomplete="username"
                            required
                            autofocus
                            @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                        >
                    </div>
                    @if ($errors->has('email') && $errors->first('email') !== 'As credenciais informadas são inválidas.')
                        <p class="field-error" id="email-error">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <div class="field">
                    <label for="password">Senha</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Digite sua senha"
                            autocomplete="current-password"
                            required
                            @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                        >
                    </div>
                    @error('password')
                        <p class="field-error" id="password-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="forgot-row">
                    <a class="forgot-link" href="{{ route('admin.password.request') }}">Esqueceu sua senha?</a>
                </div>

                <button class="submit-button" type="submit">Entrar</button>
            </form>
        </section>

        <p class="access-note">Acesso exclusivo para administradores</p>
    </main>
</body>
</html>
