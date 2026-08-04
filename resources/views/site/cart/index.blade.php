@extends('site.layouts.app')

@section('title', 'Meu Carrinho')

@push('styles')
<style>
    .cart-page { padding: 42px 0 15px; }
    .cart-page h1 { margin: 0 0 27px; font-size: clamp(28px, 4vw, 38px); letter-spacing: -.8px; }
    .cart-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; align-items: start; gap: 27px; }
    .cart-list, .order-summary { border: 1px solid var(--border); border-radius: 15px; background: white; box-shadow: 0 11px 35px rgba(31,41,55,.06); }
    .cart-item { display: grid; grid-template-columns: 105px minmax(0, 1fr) auto auto; align-items: center; gap: 19px; padding: 20px; }
    .cart-item + .cart-item { border-top: 1px solid var(--border); }
    .cart-item-image { width: 105px; height: 87px; display: grid; place-items: center; overflow: hidden; border-radius: 10px; background: #eff0f2; color: #a2a7af; }
    .cart-item-image img { width: 100%; height: 100%; object-fit: cover; }
    .cart-item-copy h2 { margin: 0 0 7px; font-size: 16px; }
    .cart-item-copy p { margin: 0; color: var(--muted); font-size: 12px; }
    .cart-item-copy .unit-price { margin-top: 8px; color: var(--primary); font-weight: 800; }
    .cart-quantity { height: 39px; display: flex; align-items: center; overflow: hidden; border: 1px solid var(--border); border-radius: 8px; }
    .cart-quantity form { height: 100%; }
    .cart-quantity button { width: 37px; height: 100%; border: 0; background: white; cursor: pointer; font-size: 17px; }
    .cart-quantity button:hover { background: var(--input); }
    .cart-quantity span { min-width: 36px; text-align: center; font-size: 13px; font-weight: 750; }
    .cart-item-total { min-width: 105px; text-align: right; }
    .cart-item-total > span { display: block; color: var(--muted); font-size: 10px; }
    .cart-item-total > strong { display: block; margin-top: 3px; font-size: 15px; }
    .remove-item { margin-top: 10px; border: 0; background: transparent; color: #d92d20; cursor: pointer; font-size: 11px; font-weight: 700; }
    .order-summary { position: sticky; top: 95px; padding: 24px; }
    .order-summary h2 { margin: 0 0 21px; font-size: 18px; }
    .summary-row { display: flex; justify-content: space-between; gap: 20px; margin: 13px 0; color: var(--muted); font-size: 13px; }
    .summary-row strong { color: var(--text); }
    .summary-row.delivery strong { color: var(--primary); font-size: 12px; }
    .summary-total { margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border); color: var(--text); font-size: 16px; font-weight: 800; }
    .summary-total strong { color: var(--primary); font-size: 20px; }
    .checkout-button, .continue-link { width: 100%; min-height: 45px; display: grid; place-items: center; border-radius: 9px; font-size: 13px; font-weight: 800; text-decoration: none; }
    .checkout-button { margin-top: 22px; border: 0; background: var(--cta); color: white; cursor: pointer; }
    .checkout-button:hover { background: var(--cta-dark); }
    .continue-link { margin-top: 10px; border: 1px solid var(--border); background: white; color: var(--text); }
    .empty-cart { padding: 65px 25px; border: 1px solid var(--border); border-radius: 15px; background: white; text-align: center; }
    .empty-cart svg { color: #b4b8bf; }
    .empty-cart h2 { margin: 18px 0 8px; font-size: 22px; }
    .empty-cart p { margin: 0 0 22px; color: var(--muted); font-size: 13px; }
    .empty-cart a { display: inline-grid; min-height: 43px; padding: 0 22px; place-items: center; border-radius: 8px; background: var(--primary); color: white; font-size: 13px; font-weight: 800; text-decoration: none; }
    @media (max-width: 850px) { .cart-layout { grid-template-columns: 1fr; } .order-summary { position: static; } }
    @media (max-width: 600px) { .cart-item { grid-template-columns: 78px 1fr; } .cart-item-image { width: 78px; height: 72px; } .cart-quantity { justify-self: start; margin-left: 97px; } .cart-item-total { grid-column: 2; grid-row: 2; } }
</style>
@endpush

@section('content')
    <section class="cart-page container">
        <h1>Meu Carrinho</h1>

        @if ($cart['is_empty'])
            <div class="empty-cart">
                <svg viewBox="0 0 24 24" width="58" height="58" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M3 3h2l2.2 11h10.7l2-7H6"></path><circle cx="9" cy="19" r="1.5"></circle><circle cx="18" cy="19" r="1.5"></circle></svg>
                <h2>Seu carrinho está vazio</h2>
                <p>Explore o cardápio e escolha seus produtos favoritos.</p>
                <a href="{{ route('site.home') }}">Ver Cardápio</a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-list">
                    @foreach ($cart['items'] as $item)
                        <article class="cart-item">
                            <a class="cart-item-image" href="{{ route('site.products.show', $item['product']) }}">
                                @if ($item['product']->image_url)
                                    <img src="{{ asset('storage/'.$item['product']->image_url) }}" alt="{{ $item['product']->name }}">
                                @else
                                    <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="m21 15-5-5L5 21"></path></svg>
                                @endif
                            </a>
                            <div class="cart-item-copy">
                                <h2>{{ $item['product']->name }}</h2>
                                <p>{{ $item['product']->description ? \Illuminate\Support\Str::limit($item['product']->description, 65) : 'Produto Foodflix' }}</p>
                                <p class="unit-price">R$ {{ number_format($item['unit_price_cents'] / 100, 2, ',', '.') }} cada</p>
                            </div>
                            <div class="cart-quantity" aria-label="Quantidade de {{ $item['product']->name }}">
                                <form method="POST" action="{{ route('site.cart.items.update', $item['product']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                    <button type="submit" aria-label="Diminuir quantidade">−</button>
                                </form>
                                <span>{{ $item['quantity'] }}</span>
                                <form method="POST" action="{{ route('site.cart.items.update', $item['product']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}">
                                    <button type="submit" aria-label="Aumentar quantidade">+</button>
                                </form>
                            </div>
                            <div class="cart-item-total">
                                <span>Subtotal</span>
                                <strong>R$ {{ number_format($item['subtotal_cents'] / 100, 2, ',', '.') }}</strong>
                                <form method="POST" action="{{ route('site.cart.items.destroy', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove-item" type="submit">Remover</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="order-summary" aria-labelledby="summary-title">
                    <h2 id="summary-title">Resumo do Pedido</h2>
                    <div class="summary-row"><span>Subtotal</span><strong>R$ {{ number_format($cart['subtotal_cents'] / 100, 2, ',', '.') }}</strong></div>
                    <div class="summary-row delivery"><span>Taxa de entrega</span><strong>A calcular</strong></div>
                    <div class="summary-row summary-total"><span>Total</span><strong>R$ {{ number_format($cart['total_cents'] / 100, 2, ',', '.') }}</strong></div>
                    <a class="checkout-button" href="{{ route('site.checkout.create') }}">Ir para o Pagamento</a>
                    <a class="continue-link" href="{{ route('site.home') }}">Continuar Comprando</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
