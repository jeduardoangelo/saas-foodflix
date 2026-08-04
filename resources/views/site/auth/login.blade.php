@extends('site.layouts.app')

@section('title', 'Acesse sua Conta')

@push('styles')
    @include('site.auth._styles')
@endpush

@section('content')
    <div class="container auth-page">
        <div class="auth-shell login-width">
            <header class="auth-heading">
                <h1>Acesse sua Conta</h1>
                <p>Entre para continuar seu pedido no Foodflix</p>
            </header>

            <section class="auth-card" aria-labelledby="login-title">
                <h2 class="auth-section-title" id="login-title"><span aria-hidden="true">✓</span> Dados de acesso</h2>

                @if (session('status')) <div class="auth-success" role="status">{{ session('status') }}</div> @endif

                @if ($errors->first('email') === 'As credenciais informadas são inválidas.')
                    <div class="auth-alert" role="alert">{{ $errors->first('email') }}</div>
                @endif

                <form method="POST" action="{{ route('site.login.store') }}">
                    @csrf

                    <div class="auth-grid">
                        <div class="field full">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="seu@email.com" autocomplete="username" required autofocus @if ($errors->has('email')) aria-invalid="true" @endif>
                            @if ($errors->has('email') && $errors->first('email') !== 'As credenciais informadas são inválidas.')
                                <p class="field-error">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <div class="field full">
                            <label for="password">Senha</label>
                            <input id="password" name="password" type="password" placeholder="Digite sua senha" autocomplete="current-password" required @if ($errors->has('password')) aria-invalid="true" @endif>
                            @error('password') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="auth-links">
                        <a href="{{ route('site.password.request') }}">Esqueceu sua senha?</a>
                        <span>Ainda não tem conta? <a href="{{ route('site.register') }}">Cadastre-se</a></span>
                    </div>

                    <div class="auth-actions">
                        <button class="auth-submit" type="submit">Entrar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
