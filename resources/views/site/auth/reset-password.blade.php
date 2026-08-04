@extends('site.layouts.app')

@section('title', 'Redefinir senha')

@push('styles')
    @include('site.auth._styles')
@endpush

@section('content')
    <div class="container auth-page">
        <div class="auth-shell login-width">
            <header class="auth-heading"><h1>Redefinir senha</h1><p>Crie uma nova senha para sua conta</p></header>
            <form class="auth-card" method="POST" action="{{ route('site.password.update') }}">
                @csrf
                <input name="token" type="hidden" value="{{ $token }}">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="password">Nova senha</label>
                    <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirme a nova senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="auth-actions"><button class="auth-submit" type="submit">Redefinir senha</button></div>
            </form>
        </div>
    </div>
@endsection
