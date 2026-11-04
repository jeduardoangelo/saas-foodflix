@extends('site.layouts.app')

@section('title', $search ? 'Busca no Cardápio' : 'Cardápio')

@push('styles')
<style>
    .hero { padding: 28px 0 8px; }
    .hero-banner { min-height: 360px; position: relative; overflow: hidden; border-radius: 18px; background: #8f2118; color: white; box-shadow: 0 18px 45px rgba(232,39,26,.2); }
    .hero-slide { position: absolute; inset: 0; display: flex; align-items: center; padding: 42px 54px; visibility: hidden; opacity: 0; transition: opacity .55s ease, visibility .55s; }
    .hero-slide.is-active { visibility: visible; opacity: 1; }
    .hero-image, .hero-overlay { position: absolute; inset: 0; width: 100%; height: 100%; }
    .hero-image { object-fit: cover; object-position: center; }
    .hero-overlay { background: linear-gradient(90deg, rgba(46,10,8,.89) 0%, rgba(66,13,9,.76) 40%, rgba(70,13,8,.08) 100%); }
    .hero-copy { position: relative; z-index: 2; width: min(100%, 540px); }
    .hero-eyebrow { display: inline-block; margin-bottom: 13px; padding: 6px 11px; border-radius: 999px; background: rgba(255,255,255,.16); font-size: 11px; font-weight: 750; letter-spacing: .5px; text-transform: uppercase; }
    .hero h1 { max-width: 520px; margin: 0 0 12px; font-size: clamp(32px, 4.8vw, 55px); line-height: 1.02; letter-spacing: -1.8px; }
    .hero p { max-width: 455px; margin: 0 0 24px; color: rgba(255,255,255,.88); font-size: 15px; line-height: 1.55; }
    .hero-link { min-height: 44px; display: inline-flex; align-items: center; padding: 0 18px; border-radius: 8px; background: white; color: var(--primary-dark); font-size: 13px; font-weight: 800; text-decoration: none; }
    .hero-controls { position: absolute; z-index: 4; right: 25px; bottom: 22px; display: flex; align-items: center; gap: 9px; }
    .hero-arrow { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid rgba(255,255,255,.52); border-radius: 50%; background: rgba(30,10,8,.3); color: white; cursor: pointer; }
    .hero-arrow:hover, .hero-arrow:focus-visible { background: rgba(255,255,255,.2); }
    .hero-arrow svg { width: 17px; height: 17px; }
    .hero-dots { display: flex; align-items: center; gap: 7px; margin: 0 3px; }
    .hero-dot { width: 9px; height: 9px; padding: 0; border: 0; border-radius: 50%; background: rgba(255,255,255,.55); cursor: pointer; }
    .hero-dot[aria-current="true"] { width: 24px; border-radius: 999px; background: white; }

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

    @media (max-width: 1020px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 760px) { .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hero-banner { min-height: 340px; } .hero-slide { padding: 35px 28px 65px; } .hero-copy { width: 78%; } .hero-overlay { background: linear-gradient(90deg, rgba(46,10,8,.9), rgba(66,13,9,.72) 58%, rgba(70,13,8,.28)); } }
    @media (max-width: 500px) { .product-grid { grid-template-columns: 1fr; } .hero-copy { width: 100%; } .hero-banner { min-height: 390px; } .hero-image { object-position: 62% center; } .hero-controls { right: 16px; bottom: 15px; } .section-title { align-items: flex-start; flex-direction: column; } }
    @media (prefers-reduced-motion: reduce) { .hero-slide, .product-card, .product-card-image img { transition: none; } }
</style>
@endpush

@section('content')
    @if (! $search)
        @php
            $heroSlides = [
                ['eyebrow' => 'Feito na hora', 'title' => 'Seu pedido favorito, do seu jeito.', 'description' => 'Lanches preparados na hora para deixar o seu dia mais saboroso.', 'link' => '#catalogo', 'label' => 'Conhecer o cardápio', 'image' => 'images/hero-burger.png'],
                ['eyebrow' => 'Para compartilhar', 'title' => 'Uma boa pizza sempre reúne todo mundo.', 'description' => 'Sabores marcantes para transformar qualquer momento em uma ocasião especial.', 'link' => '#categoria-pizzas', 'label' => 'Explorar pizzas', 'image' => 'images/hero-pizza.png'],
                ['eyebrow' => 'Clássico brasileiro', 'title' => 'Pastel quentinho, sabor de verdade.', 'description' => 'Crocante por fora, recheado por dentro e perfeito para matar a fome.', 'link' => '#categoria-pasteis', 'label' => 'Ver pastéis', 'image' => 'images/hero-pastel.png'],
            ];
        @endphp
        <section class="hero">
            <div class="container">
                <div class="hero-banner" data-hero-carousel role="region" aria-roledescription="carousel" aria-label="Destaques do cardápio">
                    @foreach ($heroSlides as $slide)
                        <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-hero-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} de {{ count($heroSlides) }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                            <img class="hero-image" src="{{ asset($slide['image']) }}" alt="" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                            <div class="hero-overlay" aria-hidden="true"></div>
                            <div class="hero-copy">
                                <span class="hero-eyebrow">{{ $slide['eyebrow'] }}</span>
                                <h1>{{ $slide['title'] }}</h1>
                                <p>{{ $slide['description'] }}</p>
                                <a class="hero-link" href="{{ $slide['link'] }}">{{ $slide['label'] }}</a>
                            </div>
                        </article>
                    @endforeach
                    <div class="hero-controls" aria-label="Controles do carrossel">
                        <button class="hero-arrow" type="button" data-hero-prev aria-label="Destaque anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></button>
                        <div class="hero-dots" role="group" aria-label="Escolher destaque">
                            @foreach ($heroSlides as $index => $slide)
                                <button class="hero-dot" type="button" data-hero-dot="{{ $index }}" aria-label="Mostrar destaque {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <button class="hero-arrow" type="button" data-hero-next aria-label="Próximo destaque"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                    </div>
                </div>
            </div>
        </section>

        <section class="catalog-section container" aria-labelledby="featured-title">
            <div class="section-title">
                <div><h2 id="featured-title">Os Mais Pedidos</h2><p>Os favoritos dos nossos clientes, com base nos pedidos aprovados.</p></div>
            </div>
            @if ($featuredProducts->isNotEmpty())
                <div class="product-grid">
                    @foreach ($featuredProducts as $product)
                        @include('site.catalog._product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="empty-catalog" style="margin-top: 0; padding: 30px 20px;">
                    <p style="margin: 0;">Os mais pedidos aparecerão aqui assim que houver pedidos aprovados.</p>
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

@push('scripts')
<script>
    (function () {
        var carousel = document.querySelector('[data-hero-carousel]');

        if (!carousel) return;

        var slides = Array.prototype.slice.call(carousel.querySelectorAll('[data-hero-slide]'));
        var dots = Array.prototype.slice.call(carousel.querySelectorAll('[data-hero-dot]'));
        var activeIndex = 0;
        var timer;
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function showSlide(index) {
            activeIndex = (index + slides.length) % slides.length;
            slides.forEach(function (slide, slideIndex) {
                var active = slideIndex === activeIndex;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach(function (dot, dotIndex) {
                dot.setAttribute('aria-current', dotIndex === activeIndex ? 'true' : 'false');
            });
        }

        function startTimer() {
            window.clearInterval(timer);
            if (!reducedMotion) timer = window.setInterval(function () { showSlide(activeIndex + 1); }, 6000);
        }

        carousel.querySelector('[data-hero-prev]').addEventListener('click', function () {
            showSlide(activeIndex - 1);
            startTimer();
        });
        carousel.querySelector('[data-hero-next]').addEventListener('click', function () {
            showSlide(activeIndex + 1);
            startTimer();
        });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showSlide(Number(dot.getAttribute('data-hero-dot')));
                startTimer();
            });
        });
        carousel.addEventListener('mouseenter', function () { window.clearInterval(timer); });
        carousel.addEventListener('mouseleave', startTimer);
        carousel.addEventListener('focusin', function () { window.clearInterval(timer); });
        carousel.addEventListener('focusout', function (event) {
            if (!carousel.contains(event.relatedTarget)) startTimer();
        });

        startTimer();
    })();
</script>
@endpush
