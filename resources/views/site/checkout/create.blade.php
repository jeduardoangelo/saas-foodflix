@extends('site.layouts.app')

@section('title', 'Finalizar Pedido')

@push('styles')
<style>
    .checkout-page { padding: 40px 0 15px; }
    .checkout-page > h1 { margin: 0 0 26px; font-size: clamp(28px, 4vw, 38px); letter-spacing: -.9px; }
    .checkout-layout { display: grid; grid-template-columns: minmax(0, 1fr) 350px; align-items: start; gap: 27px; }
    .checkout-form { display: grid; gap: 19px; }
    .checkout-section, .checkout-summary { padding: 25px; border: 1px solid var(--border); border-radius: 15px; background: white; box-shadow: 0 11px 35px rgba(31,41,55,.055); }
    .checkout-section h2, .checkout-summary h2 { display: flex; align-items: center; gap: 10px; margin: 0 0 20px; font-size: 17px; }
    .step { width: 29px; height: 29px; display: grid; place-items: center; border-radius: 8px; background: var(--primary-soft); color: var(--primary); font-size: 12px; }
    .choice-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
    .choice { position: relative; display: flex; align-items: flex-start; gap: 11px; padding: 16px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; }
    .choice:has(input:checked) { border-color: var(--primary); background: var(--primary-soft); }
    .choice input { margin-top: 3px; accent-color: var(--primary); }
    .choice strong { display: block; font-size: 13px; }
    .choice small { display: block; margin-top: 4px; color: var(--muted); font-size: 11px; line-height: 1.45; }
    .address-fields { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(100px, .5fr) minmax(0, 1fr); gap: 15px; }
    .address-fields.hidden { display: none; }
    .field.full { grid-column: 1 / -1; }
    .field label { display: block; margin-bottom: 7px; color: #343a43; font-size: 12px; font-weight: 750; }
    .field input { width: 100%; height: 44px; padding: 0 13px; border: 1px solid #dfe1e6; border-radius: 8px; outline: 0; background: #fafafa; font-size: 13px; }
    .field input:focus { border-color: rgba(240,78,35,.65); background: white; box-shadow: 0 0 0 3px rgba(240,78,35,.08); }
    .field-error, .payment-error { margin: 6px 0 0; color: #c62828; font-size: 11px; }
    .pickup-note { display: none; padding: 15px; border-radius: 9px; background: #f7f7f8; color: var(--muted); font-size: 12px; line-height: 1.55; }
    .pickup-note.visible { display: block; }
    .checkout-summary { position: sticky; top: 94px; }
    .summary-items { max-height: 245px; overflow: auto; }
    .summary-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 13px; padding: 11px 0; border-bottom: 1px solid var(--border); font-size: 12px; }
    .summary-item span { display: block; color: var(--muted); font-size: 10px; margin-top: 3px; }
    .summary-values { padding-top: 12px; }
    .summary-row { display: flex; justify-content: space-between; gap: 20px; margin: 12px 0; color: var(--muted); font-size: 12px; }
    .summary-row strong { color: var(--text); }
    .summary-total { margin-top: 17px; padding-top: 16px; border-top: 1px solid var(--border); color: var(--text); font-size: 15px; font-weight: 800; }
    .summary-total strong { color: var(--primary); font-size: 20px; }
    .confirm-button { width: 100%; min-height: 47px; margin-top: 20px; border: 0; border-radius: 9px; background: var(--cta); color: white; cursor: pointer; font-size: 13px; font-weight: 800; box-shadow: 0 8px 20px rgba(232,39,26,.18); }
    .confirm-button:hover { background: var(--cta-dark); }
    .secure-note { margin: 12px 0 0; color: var(--muted); text-align: center; font-size: 10px; line-height: 1.5; }
    @media (max-width: 880px) { .checkout-layout { grid-template-columns: 1fr; } .checkout-summary { position: static; } }
    @media (max-width: 620px) { .choice-grid, .address-fields { grid-template-columns: 1fr; } .field.full { grid-column: auto; } .checkout-section, .checkout-summary { padding: 21px 17px; } }
</style>
@endpush

@section('content')
    @php
        $deliveryMethod = old('delivery_method', 'entrega');
        $addressValue = function ($field) use ($defaultAddress) {
            return old($field, $defaultAddress ? $defaultAddress->{$field} : '');
        };
        $initialDeliveryFeeCents = $deliveryMethod === 'entrega' ? $deliveryFeeCents : 0;
        $initialTotalCents = $cart['subtotal_cents'] + $initialDeliveryFeeCents;
    @endphp
    <section class="checkout-page container" data-checkout data-subtotal="{{ $cart['subtotal_cents'] }}" data-delivery-fee="{{ $deliveryFeeCents }}">
        <h1>Finalizar Pedido</h1>

        <form method="POST" action="{{ route('site.checkout.store') }}">
            @csrf
            <div class="checkout-layout">
                <div class="checkout-form">
                    <section class="checkout-section" aria-labelledby="delivery-title">
                        <h2 id="delivery-title"><span class="step">1</span> Como você quer receber?</h2>
                        <div class="choice-grid">
                            <label class="choice">
                                <input type="radio" name="delivery_method" value="entrega" {{ $deliveryMethod === 'entrega' ? 'checked' : '' }}>
                                <span><strong>Entrega</strong><small>Receba no endereço informado abaixo.</small></span>
                            </label>
                            <label class="choice">
                                <input type="radio" name="delivery_method" value="retirada" {{ $deliveryMethod === 'retirada' ? 'checked' : '' }}>
                                <span><strong>Retirar no Local</strong><small>Sem taxa de entrega.</small></span>
                            </label>
                        </div>
                        @error('delivery_method') <p class="field-error">{{ $message }}</p> @enderror
                    </section>

                    <section class="checkout-section" aria-labelledby="address-title">
                        <h2 id="address-title"><span class="step">2</span> Endereço de Entrega</h2>
                        <div class="address-fields {{ $deliveryMethod === 'retirada' ? 'hidden' : '' }}" data-address-fields>
                            <div class="field">
                                <label for="street">Rua ou avenida</label>
                                <input id="street" name="street" type="text" value="{{ $addressValue('street') }}" maxlength="255" data-address-required>
                                @error('street') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="field">
                                <label for="number">Número</label>
                                <input id="number" name="number" type="text" value="{{ $addressValue('number') }}" maxlength="20" data-address-required>
                                @error('number') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="field">
                                <label for="neighborhood">Bairro</label>
                                <input id="neighborhood" name="neighborhood" type="text" value="{{ $addressValue('neighborhood') }}" maxlength="100" data-address-required>
                                @error('neighborhood') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="field">
                                <label for="city">Cidade</label>
                                <input id="city" name="city" type="text" value="{{ $addressValue('city') }}" maxlength="100" data-address-required>
                                @error('city') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="field full">
                                <label for="complement">Complemento (opcional)</label>
                                <input id="complement" name="complement" type="text" value="{{ $addressValue('complement') }}" maxlength="255">
                                @error('complement') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="pickup-note {{ $deliveryMethod === 'retirada' ? 'visible' : '' }}" data-pickup-note>
                            Retirada em: {{ $storeSettings->physical_address }}. Consulte o horário: {{ $storeSettings->opening_hours }}.
                        </div>
                    </section>

                    <section class="checkout-section" aria-labelledby="payment-title">
                        <h2 id="payment-title"><span class="step">3</span> Forma de Pagamento</h2>
                        <div class="choice-grid">
                            <label class="choice">
                                <input type="radio" name="payment_method" value="cartao_credito" {{ old('payment_method', 'cartao_credito') === 'cartao_credito' ? 'checked' : '' }}>
                                <span><strong>Cartão de Crédito</strong><small>Pagamento online simulado no ambiente de desenvolvimento.</small></span>
                            </label>
                            <label class="choice">
                                <input type="radio" name="payment_method" value="pix" {{ old('payment_method') === 'pix' ? 'checked' : '' }}>
                                <span><strong>Pix</strong><small>Aprovação simulada pelo gateway de desenvolvimento.</small></span>
                            </label>
                        </div>
                        @error('payment_method') <p class="payment-error" role="alert">{{ $message }}</p> @enderror
                    </section>
                </div>

                <aside class="checkout-summary" aria-labelledby="summary-title">
                    <h2 id="summary-title">Resumo do Pedido</h2>
                    <div class="summary-items">
                        @foreach ($cart['items'] as $item)
                            <div class="summary-item">
                                <div><strong>{{ $item['product']->name }}</strong><span>{{ $item['quantity'] }} × R$ {{ number_format($item['unit_price_cents'] / 100, 2, ',', '.') }}</span></div>
                                <strong>R$ {{ number_format($item['subtotal_cents'] / 100, 2, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <div class="summary-values">
                        <div class="summary-row"><span>Subtotal</span><strong>R$ {{ number_format($cart['subtotal_cents'] / 100, 2, ',', '.') }}</strong></div>
                        <div class="summary-row"><span>Taxa de entrega</span><strong data-delivery-fee-label>R$ {{ number_format($initialDeliveryFeeCents / 100, 2, ',', '.') }}</strong></div>
                        <div class="summary-row summary-total"><span>Total</span><strong data-checkout-total>R$ {{ number_format($initialTotalCents / 100, 2, ',', '.') }}</strong></div>
                    </div>
                    <button class="confirm-button" type="submit">Confirmar e Pagar</button>
                    <p class="secure-note">Nenhum dado sensível de cartão é armazenado pelo Foodflix.</p>
                </aside>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        var checkout = document.querySelector('[data-checkout]');
        if (!checkout) return;

        var subtotal = Number(checkout.dataset.subtotal);
        var deliveryFee = Number(checkout.dataset.deliveryFee);
        var addressFields = checkout.querySelector('[data-address-fields]');
        var pickupNote = checkout.querySelector('[data-pickup-note]');
        var feeLabel = checkout.querySelector('[data-delivery-fee-label]');
        var totalLabel = checkout.querySelector('[data-checkout-total]');
        var formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

        function renderDelivery(method) {
            var delivery = method === 'entrega';
            addressFields.classList.toggle('hidden', !delivery);
            pickupNote.classList.toggle('visible', !delivery);
            addressFields.querySelectorAll('[data-address-required]').forEach(function (input) {
                input.required = delivery;
            });
            feeLabel.textContent = formatter.format((delivery ? deliveryFee : 0) / 100);
            totalLabel.textContent = formatter.format((subtotal + (delivery ? deliveryFee : 0)) / 100);
        }

        checkout.querySelectorAll('input[name="delivery_method"]').forEach(function (input) {
            input.addEventListener('change', function () { renderDelivery(input.value); });
            if (input.checked) renderDelivery(input.value);
        });
    })();
</script>
@endpush
