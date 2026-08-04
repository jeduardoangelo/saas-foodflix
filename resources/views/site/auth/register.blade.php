@extends('site.layouts.app')

@section('title', 'Crie sua Conta')

@push('styles')
    @include('site.auth._styles')
@endpush

@section('content')
    <div class="container auth-page">
        <div class="auth-shell">
            <header class="auth-heading">
                <h1>Crie sua Conta</h1>
                <p>Preencha seus dados para pedir com mais praticidade</p>
            </header>

            <form class="auth-card" method="POST" action="{{ route('site.register.store') }}">
                @csrf

                <section class="auth-section" aria-labelledby="personal-title">
                    <h2 class="auth-section-title" id="personal-title"><span aria-hidden="true">1</span> Dados Pessoais</h2>
                    <div class="auth-grid">
                        <div class="field full">
                            <label for="name">Nome completo</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" autocomplete="name" required autofocus @if ($errors->has('name')) aria-invalid="true" @endif>
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="cpf">CPF</label>
                            <input id="cpf" name="cpf" type="text" value="{{ old('cpf') }}" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" autocomplete="off" required @if ($errors->has('cpf')) aria-invalid="true" @endif>
                            @error('cpf') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required @if ($errors->has('email')) aria-invalid="true" @endif>
                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="auth-section" aria-labelledby="address-title">
                    <h2 class="auth-section-title" id="address-title"><span aria-hidden="true">2</span> Endereço de Entrega</h2>
                    <div class="auth-grid three">
                        <div class="field">
                            <label for="street">Rua ou avenida</label>
                            <input id="street" name="street" type="text" value="{{ old('street') }}" maxlength="255" autocomplete="address-line1" required @if ($errors->has('street')) aria-invalid="true" @endif>
                            @error('street') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="number">Número</label>
                            <input id="number" name="number" type="text" value="{{ old('number') }}" maxlength="20" required @if ($errors->has('number')) aria-invalid="true" @endif>
                            @error('number') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="neighborhood">Bairro</label>
                            <input id="neighborhood" name="neighborhood" type="text" value="{{ old('neighborhood') }}" maxlength="100" required @if ($errors->has('neighborhood')) aria-invalid="true" @endif>
                            @error('neighborhood') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="city">Cidade</label>
                            <input id="city" name="city" type="text" value="{{ old('city') }}" maxlength="100" autocomplete="address-level2" required @if ($errors->has('city')) aria-invalid="true" @endif>
                            @error('city') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field full">
                            <label for="complement">Complemento (opcional)</label>
                            <input id="complement" name="complement" type="text" value="{{ old('complement') }}" maxlength="255" autocomplete="address-line2">
                            @error('complement') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="auth-section" aria-labelledby="security-title">
                    <h2 class="auth-section-title" id="security-title"><span aria-hidden="true">3</span> Segurança</h2>
                    <div class="auth-grid">
                        <div class="field">
                            <label for="password">Senha</label>
                            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required @if ($errors->has('password')) aria-invalid="true" @endif>
                            @error('password') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirme a senha</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                </section>

                <div class="privacy-consent">
                    <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" required {{ old('privacy_accepted') ? 'checked' : '' }} @if ($errors->has('privacy_accepted')) aria-invalid="true" @endif>
                    <label for="privacy_accepted">
                        Li e aceito a <a href="{{ route('site.privacy') }}" target="_blank" rel="noopener">Política de Privacidade</a>.
                    </label>
                </div>
                @error('privacy_accepted') <p class="field-error">{{ $message }}</p> @enderror

                <p class="required-note">Todos os campos são obrigatórios, exceto o complemento.</p>
                <div class="auth-actions">
                    <button class="auth-submit" type="submit">Criar minha conta</button>
                </div>
                <div class="auth-links center">
                    <span>Já tem uma conta? <a href="{{ route('site.login') }}">Entrar</a></span>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var cpf = document.getElementById('cpf');
        cpf.addEventListener('input', function () {
            var digits = cpf.value.replace(/\D/g, '').slice(0, 11);
            cpf.value = digits
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        });
    })();
</script>
@endpush
