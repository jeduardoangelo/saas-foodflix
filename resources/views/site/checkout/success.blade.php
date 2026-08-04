@extends('site.layouts.app')

@section('title', 'Pedido Confirmado')

@push('styles')
<style>
    .success-page { padding: 60px 0 15px; }
    .success-card { width: min(100%, 620px); margin: 0 auto; padding: 38px; border: 1px solid var(--border); border-radius: 17px; background: white; box-shadow: 0 15px 45px rgba(31,41,55,.07); text-align: center; }
    .success-icon { width: 65px; height: 65px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 50%; background: #eaf8ef; color: #16803a; font-size: 30px; font-weight: 850; }
    .success-card h1 { margin: 0 0 8px; font-size: 29px; }
    .success-card > p { margin: 0; color: var(--muted); font-size: 13px; }
    .order-number { margin: 22px 0; padding: 15px; border-radius: 9px; background: var(--primary-soft); }
    .order-number span { display: block; color: var(--muted); font-size: 10px; text-transform: uppercase; }
    .order-number strong { display: block; margin-top: 4px; color: var(--primary); font-size: 21px; }
    .success-summary { margin: 0 0 24px; text-align: left; }
    .success-row { display: flex; justify-content: space-between; gap: 20px; padding: 10px 0; border-bottom: 1px solid var(--border); color: var(--muted); font-size: 12px; }
    .success-row strong { color: var(--text); }
    .success-actions { display: grid; gap: 9px; }
    .success-actions a { min-height: 44px; display: grid; place-items: center; border-radius: 9px; background: var(--primary); color: white; font-size: 13px; font-weight: 800; text-decoration: none; }
    .success-actions a.secondary { border: 1px solid var(--border); background: white; color: var(--text); }
    @media (max-width: 520px) { .success-card { padding: 28px 19px; } }
</style>
@endpush

@section('content')
    <section class="success-page container">
        <div class="success-card">
            <div class="success-icon" aria-hidden="true">✓</div>
            <h1>Pedido confirmado!</h1>
            <p>Pagamento aprovado e pedido enviado para a loja.</p>

            <div class="order-number">
                <span>Número do pedido</span>
                <strong>#{{ $order->order_number }}</strong>
            </div>

            <div class="success-summary">
                <div class="success-row"><span>Forma de recebimento</span><strong>{{ $order->delivery_method === 'entrega' ? 'Entrega' : 'Retirada no local' }}</strong></div>
                <div class="success-row"><span>Pagamento</span><strong>{{ $order->payment_method === 'pix' ? 'Pix' : 'Cartão de crédito' }}</strong></div>
                <div class="success-row"><span>Status</span><strong>Pago</strong></div>
                <div class="success-row"><span>Total</span><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
            </div>

            <div class="success-actions">
                <a href="{{ route('site.orders.show', $order) }}">Acompanhar Pedido</a>
                <a class="secondary" href="{{ route('site.home') }}">Voltar ao Cardápio</a>
            </div>
        </div>
    </section>
@endsection
