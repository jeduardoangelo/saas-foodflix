<div class="mini-cart {{ session('cart_open') ? 'open' : '' }}" data-mini-cart role="dialog" aria-label="Resumo do carrinho">
    <div class="mini-cart-header">
        <strong>Meu Carrinho</strong>
        <span>{{ $cartSummary['item_count'] }} {{ $cartSummary['item_count'] === 1 ? 'item' : 'itens' }}</span>
    </div>

    @if ($cartSummary['is_empty'])
        <div class="mini-cart-empty">
            <p>Seu carrinho está vazio.</p>
            <a href="{{ route('site.home') }}">Ver cardápio</a>
        </div>
    @else
        <div class="mini-cart-items">
            @foreach ($cartSummary['items'] as $item)
                <article class="mini-cart-item">
                    <div class="mini-cart-image">
                        @if ($item['product']->image_url)
                            <img src="{{ asset('storage/'.$item['product']->image_url) }}" alt="{{ $item['product']->name }}">
                        @else
                            <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="m21 15-5-5L5 21"></path></svg>
                        @endif
                    </div>
                    <div class="mini-cart-copy">
                        <strong>{{ $item['product']->name }}</strong>
                        <span>R$ {{ number_format($item['unit_price_cents'] / 100, 2, ',', '.') }}</span>
                        <div class="mini-quantity">
                            <form method="POST" action="{{ route('site.cart.items.update', $item['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                <input type="hidden" name="mini_cart" value="1">
                                <button type="submit" aria-label="Diminuir {{ $item['product']->name }}">−</button>
                            </form>
                            <span>{{ $item['quantity'] }}</span>
                            <form method="POST" action="{{ route('site.cart.items.update', $item['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}">
                                <input type="hidden" name="mini_cart" value="1">
                                <button type="submit" aria-label="Aumentar {{ $item['product']->name }}">+</button>
                            </form>
                        </div>
                    </div>
                    <div class="mini-cart-side">
                        <form method="POST" action="{{ route('site.cart.items.destroy', $item['product']) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="mini_cart" value="1">
                            <button type="submit" aria-label="Remover {{ $item['product']->name }}">×</button>
                        </form>
                        <strong>R$ {{ number_format($item['subtotal_cents'] / 100, 2, ',', '.') }}</strong>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mini-cart-footer">
            <div><span>Subtotal</span><strong>R$ {{ number_format($cartSummary['subtotal_cents'] / 100, 2, ',', '.') }}</strong></div>
            <a href="{{ route('site.cart.index') }}">Ver Carrinho</a>
        </div>
    @endif
</div>
