@extends('admin.layouts.app')

@section('title', 'Configurações da Loja')

@push('styles')
<style>
    .settings-form { display: grid; gap: 20px; }
    .settings-section { padding: 27px; }
    .section-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 23px; padding-bottom: 17px; border-bottom: 1px solid var(--border); }
    .section-icon { width: 38px; height: 38px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 9px; background: var(--primary-soft); color: var(--primary); }
    .section-icon svg { width: 20px; height: 20px; }
    .section-heading h2 { margin: 0 0 3px; font-size: 17px; }
    .section-heading p { margin: 0; color: var(--muted); font-size: 12px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 19px; }
    .field.full { grid-column: 1 / -1; }
    .field label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 650; }
    .required { color: var(--danger); }
    .field input, .field textarea { width: 100%; border: 1px solid transparent; border-radius: 8px; outline: 0; background: var(--input); color: var(--text); font-size: 14px; }
    .field input { height: 47px; padding: 0 13px; }
    .field textarea { min-height: 105px; padding: 13px; resize: vertical; }
    .field input:focus, .field textarea:focus { border-color: rgba(240,78,35,.55); background: white; box-shadow: 0 0 0 3px rgba(240,78,35,.09); }
    .field-error { margin: 6px 1px 0; color: var(--danger); font-size: 12px; }
    .field-help { margin: 6px 1px 0; color: var(--muted); font-size: 11px; }
    .placeholder-warning { display: flex; gap: 10px; margin-bottom: 20px; padding: 14px 16px; border: 1px solid #fed7aa; border-radius: 9px; background: #fff7ed; color: #9a3412; font-size: 13px; line-height: 1.45; }
    .placeholder-warning svg { width: 19px; height: 19px; flex: 0 0 auto; }
    .settings-actions { display: flex; justify-content: flex-end; padding-top: 2px; }
    @media (max-width: 720px) { .form-grid { grid-template-columns: 1fr; } .field.full { grid-column: auto; } }
</style>
@endpush

@section('content')
    <div class="page-heading">
        <div>
            <h1>Configurações da Loja</h1>
            <p>Atualize os dados institucionais e operacionais do Foodflix.</p>
        </div>
    </div>

    @if ($settings->cnpj === '00.000.000/0001-00' || strpos($settings->physical_address, 'a definir') !== false || strpos($settings->opening_hours, 'a definir') !== false)
        <div class="placeholder-warning" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10.3 2.9 1.8 17a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 2.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4M12 17h.01"></path></svg>
            <span>Esta instalação ainda possui dados de exemplo. Substitua o CNPJ, o endereço e o horário de funcionamento pelos dados reais da loja antes da produção.</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">Revise os campos destacados antes de salvar as configurações.</div>
    @endif

    <form class="settings-form" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <section class="card settings-section">
            <div class="section-heading">
                <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h6M9 14h6M9 18h6"></path></svg></span>
                <div><h2>Informações da Empresa</h2><p>Dados de identificação e localização da loja.</p></div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="store_name">Nome da loja <span class="required">*</span></label>
                    <input id="store_name" name="store_name" type="text" value="{{ old('store_name', $settings->store_name) }}" maxlength="255" required autofocus>
                    @error('store_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="cnpj">CNPJ <span class="required">*</span></label>
                    <input id="cnpj" name="cnpj" type="text" value="{{ old('cnpj', $settings->cnpj) }}" maxlength="18" placeholder="00.000.000/0000-00" required>
                    @error('cnpj') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field full">
                    <label for="physical_address">Endereço físico <span class="required">*</span></label>
                    <input id="physical_address" name="physical_address" type="text" value="{{ old('physical_address', $settings->physical_address) }}" maxlength="500" required>
                    @error('physical_address') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card settings-section">
            <div class="section-heading">
                <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span>
                <div><h2>Horário de Funcionamento</h2><p>Texto informativo exibido aos clientes.</p></div>
            </div>
            <div class="field">
                <label for="opening_hours">Horário <span class="required">*</span></label>
                <textarea id="opening_hours" name="opening_hours" required>{{ old('opening_hours', $settings->opening_hours) }}</textarea>
                <p class="field-help">Exemplo: Segunda a Sexta: 11h às 23h. O horário ainda não bloqueia pedidos automaticamente.</p>
                @error('opening_hours') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="card settings-section">
            <div class="section-heading">
                <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h13v11H3zM16 10h3l2 3v4h-5z"></path><circle cx="7" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg></span>
                <div><h2>Configurações de Entrega</h2><p>Valores usados nos novos pedidos.</p></div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="delivery_fee">Taxa de entrega (R$) <span class="required">*</span></label>
                    <input id="delivery_fee" name="delivery_fee" type="number" value="{{ old('delivery_fee', $settings->delivery_fee) }}" min="0" max="99999999.99" step="0.01" inputmode="decimal" required>
                    @error('delivery_fee') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label for="estimated_delivery_minutes">Tempo estimado (minutos) <span class="required">*</span></label>
                    <input id="estimated_delivery_minutes" name="estimated_delivery_minutes" type="number" value="{{ old('estimated_delivery_minutes', $settings->estimated_delivery_minutes) }}" min="1" step="1" required>
                    @error('estimated_delivery_minutes') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="settings-actions">
            <button class="button button-primary" type="submit">Salvar Configurações</button>
        </div>
    </form>
@endsection
