@extends('site.layouts.app')

@section('title', 'Esqueci minha senha')

@push('styles')
    @include('site.auth._styles')
@endpush

@section('content')
    <div class="container auth-page">
        <div class="auth-shell login-width">
            <header class="auth-heading">
                <h1>Esqueci minha senha</h1>
                <p>Informe seu email para receber o link de redefinição</p>
            </header>
            <form class="auth-card" method="POST" action="{{ route('site.password.email') }}">
                @csrf
                @if (session('status')) <div class="auth-success" role="status">{{ session('status') }}</div> @endif
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="auth-actions"><button class="auth-submit" type="submit">Enviar link</button></div>
                <div class="auth-links center"><a href="{{ route('site.login') }}">Voltar ao login</a></div>
            </form>
        </div>
    </div>
@endsection
