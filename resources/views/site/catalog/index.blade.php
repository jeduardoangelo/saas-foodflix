@extends('site.layouts.app')

@section('title', $search ? 'Busca no Cardápio' : 'Cardápio')

@push('styles')
<style>
    .hero { padding: 28px 0 8px; }
    .hero-banner { min-height: 285px; position: relative; display: flex; align-items: center; overflow: hidden; padding: 42px 54px; border-radius: 18px; background: linear-gradient(120deg, #f04e23 0%, #ef3b22 56%, #d92317 100%); color: white; box-shadow: 0 18px 45px rgba(232,39,26,.2); }
    .hero-copy { position: relative; z-index: 2; width: min(100%, 540px); }
    .hero-eyebrow { display: inline-block; margin-bottom: 13px; padding: 6px 11px; border-radius: 999px; background: rgba(255,255,255,.16); font-size: 11px; font-weight: 750; letter-spacing: .5px; text-transform: uppercase; }
    .hero h1 { max-width: 520px; margin: 0 0 12px; font-size: clamp(32px, 4.8vw, 55px); line-height: 1.02; letter-spacing: -1.8px; }
    .hero p { max-width: 455px; margin: 0 0 24px; color: rgba(255,255,255,.88); font-size: 15px; line-height: 1.55; }
    .hero-link { min-height: 44px; display: inline-flex; align-items: center; padding: 0 18px; border-radius: 8px; background: white; color: var(--primary-dark); font-size: 13px; font-weight: 800; text-decoration: none; }
    .hero-art { position: absolute; right: 5%; bottom: -18%; width: 320px; height: 320px; display: grid; place-items: center; border-radius: 50%; background: rgba(255,255,255,.1); transform: rotate(-8deg); }
    .hero-art svg { width: 190px; height: 190px; opacity: .95; }
    .hero-bubble { position: absolute; border-radius: 50%; background: rgba(255,255,255,.11); }
    .hero-bubble.one { width: 60px; height: 60px; top: 26px; right: 31%; }
    .hero-bubble.two { width: 24px; height: 24px; top: 44px; right: 8%; }

    .catalog-section { padding-top: 44px; scroll-margin-top: 90px; }
    .section-title { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 21px; }
    .section-title h2 { margin: 0 0 5px; font-size: 24px; letter-spacing: -.5px; }
    .section-title p { margin: 0; color: var(--muted); font-size: 13px; }
    .section-count { color: var(--muted); font-size: 12px; }
    .product-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .product-card { min-width: 0; overflow: hidden; border: 1px solid var(--border); border-radius: 13px; background: white; box-shadow: 0 7px 24px rgba(31,41,55,.05); transition: transform .2s, box-shadow .2s; }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 13px 30px rgba(31,41,55,.09); }
    .product-card-image { height: 175px; display: block; overflow: hidden; background: #eceef0; }
    .product-card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
    .product-card:hover .product-card-image img { transform: scale(1.035); }
    .image-placeholder { width: 100%; height: 100%; display: grid; place-items: center; color: #a7adb6; background: linear-gradient(135deg, #f1f2f4, #e7e9ec); }
    .product-card-body { padding: 17px; }
    .product-category { color: var(--primary); font-size: 10px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; }
    .product-card h3 { margin: 7px 0 7px; font-size: 16px; line-height: 1.25; }
    .product-card h3 a { text-decoration: none; }
    .product-card h3 a:hover { color: var(--primary); }
    .product-card p { min-height: 38px; margin: 0 0 15px; color: var(--muted); font-size: 12px; line-height: 1.5; }
    .product-card-footer { display: grid; gap: 14px; }
    .product-card-footer strong { color: var(--primary); font-size: 18px; }
    .product-actions { display: grid; grid-template-columns: 1fr .78fr; gap: 8px; }
    .details-button, .order-button { min-height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; border-radius: 7px; cursor: pointer; font-size: 11px; font-weight: 750; text-decoration: none; }
    .details-button { border: 1px solid var(--primary); color: var(--primary); background: white; }
    .order-button { border: 0; background: var(--cta); color: white; }
    .order-button:hover { background: var(--cta-dark); }
    .search-summary { padding: 35px 0 0; }
    .search-summary h1 { margin: 0 0 7px; font-size: 28px; }
    .search-summary p { margin: 0; color: var(--muted); font-size: 14px; }
    .empty-catalog { margin-top: 35px; padding: 60px 25px; border: 1px dashed #d5d8dd; border-radius: 13px; background: white; color: var(--muted); text-align: center; }
    .empty-catalog h2 { margin: 0 0 8px; color: var(--text); font-size: 20px; }

    @media (max-width: 1020px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } .hero-art { right: -4%; } }
    @media (max-width: 760px) { .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hero-banner { min-height: 320px; padding: 35px 28px; } .hero-copy { width: 75%; } .hero-art { right: -25%; opacity: .7; } }
    @media (max-width: 500px) { .product-grid { grid-template-columns: 1fr; } .hero-copy { width: 100%; } .hero-art { display: none; } .section-title { align-items: flex-start; flex-direction: column; } }
</style>
@endpush

@section('content')
    @if (! $search)
        <section class="hero">
            <div class="container">
                <div class="hero-banner">
                    <div class="hero-copy">
                        <span class="hero-eyebrow">Feito na hora</span>
                        <h1>Seu pedido favorito, do seu jeito.</h1>
                        <p>Explore o cardápio Foodflix e escolha entre lanches, pizzas, pastéis e bebidas preparados para deixar o seu dia mais saboroso.</p>
                        <a class="hero-link" href="#catalogo">Conhecer o cardápio</a>
                    </div>
                    <span class="hero-bubble one"></span><span class="hero-bubble two"></span>
                    <div class="hero-art" aria-hidden="true">
                        <svg viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"><path d="M28 101h144"></path><path d="M43 101a57 57 0 0 1 114 0"></path><path d="M100 44V27"></path><path d="M35 130h130"></path><path d="M50 130v34h100v-34"></path><path d="M75 70c10-9 39-9 50 0"></path></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="catalog-section container" aria-labelledby="featured-title">
            <div class="section-title">
                <div><h2 id="featured-title">Os Mais Pedidos</h2><p>Uma seleção especial de favoritos do Foodflix.</p></div>
            </div>
            @if ($featuredProducts->isNotEmpty())
                <div class="product-grid">
                    @foreach ($featuredProducts as $product)
                        @include('site.catalog._product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="empty-catalog" style="margin-top: 0; padding: 30px 20px;">
                    <p style="margin: 0;">Os produtos em destaque serão exibidos aqui em breve.</p>
                </div>
            @endif
        </section>
    @else
        <section class="search-summary container">
            <h1>Resultados para “{{ $search }}”</h1>
            <p>{{ $categories->sum(function ($category) { return $category->products->count(); }) }} produto(s) encontrado(s).</p>
        </section>
    @endif

    <div id="catalogo">
        @forelse ($categories as $category)
            <section class="catalog-section container" id="categoria-{{ $category->slug }}" aria-labelledby="category-{{ $category->id }}">
                <div class="section-title">
                    <div><h2 id="category-{{ $category->id }}">{{ $category->name }}</h2><p>Escolha seu favorito e veja todos os detalhes.</p></div>
                    <span class="section-count">{{ $category->products->count() }} produto(s)</span>
                </div>
                <div class="product-grid">
                    @foreach ($category->products as $product)
                        @include('site.catalog._product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @empty
            <section class="empty-catalog container">
                <h2>{{ $search ? 'Nenhum produto encontrado' : 'Cardápio em preparação' }}</h2>
                <p>{{ $search ? 'Tente buscar por outro nome.' : 'Em breve novos produtos estarão disponíveis por aqui.' }}</p>
            </section>
        @endforelse
    </div>
@endsection
