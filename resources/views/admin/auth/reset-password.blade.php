@extends('admin.auth.password-layout')

@section('title', 'Redefinir senha')

@section('form')
    <h1>Redefinir senha</h1>
    <p class="subtitle">Crie uma nova senha administrativa</p>
    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $token }}">
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="field">
            <label for="password">Nova senha</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Confirme a nova senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
        </div>
        <button type="submit">Redefinir senha</button>
    </form>
@endsection
