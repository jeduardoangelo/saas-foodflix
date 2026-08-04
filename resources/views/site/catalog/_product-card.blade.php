<article class="product-card">
    <a class="product-card-image" href="{{ route('site.products.show', $product) }}" aria-label="Ver detalhes de {{ $product->name }}">
        @if ($product->image_url)
            <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}">
        @else
            <span class="image-placeholder" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path></svg>
            </span>
        @endif
    </a>
    <div class="product-card-body">
        <span class="product-category">{{ $product->category->name }}</span>
        <h3><a href="{{ route('site.products.show', $product) }}">{{ $product->name }}</a></h3>
        <p>{{ $product->description ? \Illuminate\Support\Str::limit($product->description, 82) : 'Uma escolha deliciosa para o seu pedido.' }}</p>
        <div class="product-card-footer">
            <strong>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
            <div class="product-actions">
                <a class="details-button" href="{{ route('site.products.show', $product) }}">Ver Detalhes</a>
                <form method="POST" action="{{ route('site.cart.items.store', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="order-button" type="submit">Pedir</button>
                </form>
            </div>
        </div>
    </div>
</article>
