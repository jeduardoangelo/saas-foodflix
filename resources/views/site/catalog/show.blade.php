@extends('site.layouts.app')

@section('title', $product->name)

@push('styles')
<style>
    .product-detail { padding: 34px 0 10px; }
    .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; color: var(--muted); font-size: 12px; }
    .breadcrumb a { color: var(--primary); text-decoration: none; }
    .detail-card { display: grid; grid-template-columns: minmax(0, 1.04fr) minmax(0, .96fr); overflow: hidden; border: 1px solid var(--border); border-radius: 17px; background: white; box-shadow: 0 13px 38px rgba(31,41,55,.07); }
    .detail-image { min-height: 500px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #f0f1f3, #e5e7ea); color: #a0a6af; }
    .detail-image img { width: 100%; height: 100%; object-fit: cover; }
    .detail-copy { display: flex; flex-direction: column; padding: 45px 43px 39px; }
    .category-pill { align-self: flex-start; padding: 6px 11px; border-radius: 999px; background: var(--primary-soft); color: var(--primary); font-size: 10px; font-weight: 850; letter-spacing: .45px; text-transform: uppercase; }
    .detail-copy h1 { margin: 14px 0 12px; font-size: clamp(29px, 4vw, 42px); line-height: 1.08; letter-spacing: -1px; }
    .description { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.7; }
    .ingredients { margin-top: 26px; padding-top: 22px; border-top: 1px solid var(--border); }
    .ingredients h2 { margin: 0 0 8px; font-size: 14px; }
    .ingredients p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.65; }
    .purchase { margin-top: auto; padding-top: 29px; }
    .price-label { display: block; margin-bottom: 3px; color: var(--muted); font-size: 11px; }
    .unit-price { color: var(--primary); font-size: 27px; font-weight: 850; }
    .purchase-row { display: grid; grid-template-columns: auto 1fr; gap: 13px; margin-top: 19px; }
    .quantity { height: 50px; display: flex; align-items: center; overflow: hidden; border: 1px solid var(--border); border-radius: 9px; background: white; }
    .quantity button { width: 42px; height: 100%; border: 0; background: transparent; color: var(--text); cursor: pointer; font-size: 19px; }
    .quantity button:hover { background: var(--input); }
    .quantity input { width: 42px; height: 100%; padding: 0; border: 0; outline: 0; color: var(--text); text-align: center; font-weight: 750; -moz-appearance: textfield; }
    .quantity input::-webkit-inner-spin-button { -webkit-appearance: none; }
    .add-button { min-height: 50px; border: 0; border-radius: 9px; background: var(--cta); color: white; cursor: pointer; font-size: 13px; font-weight: 800; box-shadow: 0 8px 18px rgba(232,39,26,.18); }
    .add-button:hover { background: var(--cta-dark); }
    .total-preview { margin: 12px 1px 0; color: var(--muted); font-size: 12px; }
    .total-preview strong { color: var(--text); }
    @media (max-width: 800px) { .detail-card { grid-template-columns: 1fr; } .detail-image { min-height: 340px; max-height: 430px; } }
    @media (max-width: 500px) { .detail-copy { padding: 29px 22px; } .purchase-row { grid-template-columns: 1fr; } .quantity { width: 130px; } }
</style>
@endpush

@section('content')
    <section class="product-detail container">
        <nav class="breadcrumb" aria-label="Navegação estrutural">
            <a href="{{ route('site.home') }}">Início</a><span>/</span><a href="{{ route('site.home').'#categoria-'.$product->category->slug }}">{{ $product->category->name }}</a><span>/</span><span>{{ $product->name }}</span>
        </nav>

        <article class="detail-card">
            <div class="detail-image">
                @if ($product->image_url)
                    <img src="{{ asset('storage/'.$product->image_url) }}" alt="{{ $product->name }}">
                @else
                    <svg viewBox="0 0 24 24" width="74" height="74" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path></svg>
                @endif
            </div>

            <div class="detail-copy" data-product-detail data-unit-price="{{ $product->price }}">
                <span class="category-pill">{{ $product->category->name }}</span>
                <h1>{{ $product->name }}</h1>
                <p class="description">{{ $product->description ?: 'Sem descrição informada.' }}</p>

                <div class="ingredients">
                    <h2>Ingredientes</h2>
                    <p>{{ $product->ingredients ?: 'Ingredientes não informados.' }}</p>
                </div>

                <div class="purchase">
                    <span class="price-label">Preço unitário</span>
                    <strong class="unit-price">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
                    <div class="purchase-row">
                        <div class="quantity" aria-label="Quantidade">
                            <button type="button" data-quantity-minus aria-label="Diminuir quantidade">−</button>
                            <input type="number" value="1" min="1" max="99" data-quantity-input aria-label="Quantidade do produto">
                            <button type="button" data-quantity-plus aria-label="Aumentar quantidade">+</button>
                        </div>
                        <button class="add-button" type="button" data-cart-preview>Adicionar ao Carrinho</button>
                    </div>
                    <p class="total-preview">Total para <span data-quantity-label>1 unidade</span>: <strong data-total-price>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong></p>
                </div>
            </div>
        </article>
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        var detail = document.querySelector('[data-product-detail]');
        if (!detail) return;

        var unitPrice = Number(detail.dataset.unitPrice);
        var input = detail.querySelector('[data-quantity-input]');
        var label = detail.querySelector('[data-quantity-label]');
        var total = detail.querySelector('[data-total-price]');
        var formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

        function updateQuantity(value) {
            var quantity = Math.min(99, Math.max(1, parseInt(value, 10) || 1));
            input.value = quantity;
            label.textContent = quantity + (quantity === 1 ? ' unidade' : ' unidades');
            total.textContent = formatter.format(unitPrice * quantity);
        }

        detail.querySelector('[data-quantity-minus]').addEventListener('click', function () { updateQuantity(Number(input.value) - 1); });
        detail.querySelector('[data-quantity-plus]').addEventListener('click', function () { updateQuantity(Number(input.value) + 1); });
        input.addEventListener('input', function () { updateQuantity(input.value); });
    })();
</script>
@endpush
