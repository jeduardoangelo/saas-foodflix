@extends('admin.auth.password-layout')

@section('title', 'Esqueci minha senha')

@section('form')
    <h1>Esqueci minha senha</h1>
    <p class="subtitle">Informe o email administrativo para receber o link</p>
    @if (session('status')) <div class="success" role="status">{{ session('status') }}</div> @endif
    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button type="submit">Enviar link</button>
        <a class="back" href="{{ route('admin.login') }}">Voltar ao login</a>
    </form>
@endsection
