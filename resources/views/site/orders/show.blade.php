@extends('site.layouts.app')

@section('title', 'Pedido #'.$order->order_number)

@push('styles')
<style>
    .order-page { padding: 40px 0 15px; }
    .order-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 25px; }
    .order-heading h1 { margin: 0 0 6px; font-size: clamp(27px, 4vw, 37px); letter-spacing: -.8px; }
    .order-heading p { margin: 0; color: var(--muted); font-size: 13px; }
    .current-status { padding: 9px 14px; border-radius: 999px; background: var(--primary-soft); color: var(--primary); font-size: 11px; font-weight: 850; white-space: nowrap; }
    .status-card, .order-content-card { border: 1px solid var(--border); border-radius: 15px; background: white; box-shadow: 0 11px 35px rgba(31,41,55,.055); }
    .status-card { padding: 27px; }
    .status-card h2 { margin: 0 0 25px; font-size: 17px; }
    .status-track { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .status-step { position: relative; padding-top: 35px; text-align: center; }
    .status-step::before { content: ''; position: absolute; top: 13px; left: 0; width: 100%; height: 3px; background: #e5e7eb; }
    .status-step:first-child::before { left: 50%; width: 50%; }
    .status-step:last-child::before { width: 50%; }
    .status-point { width: 28px; height: 28px; position: absolute; top: 0; left: 50%; z-index: 1; display: grid; place-items: center; transform: translateX(-50%); border: 3px solid white; border-radius: 50%; background: #d9dce1; color: white; box-shadow: 0 0 0 1px #d9dce1; font-size: 11px; font-weight: 850; }
    .status-step.complete::before, .status-step.current::before { background: var(--primary); }
    .status-step.complete .status-point, .status-step.current .status-point { background: var(--primary); box-shadow: 0 0 0 1px var(--primary); }
    .status-step strong { display: block; font-size: 12px; }
    .status-step span { display: block; margin-top: 5px; padding: 0 8px; color: var(--muted); font-size: 10px; line-height: 1.45; }
    .refresh-note { margin: 22px 0 0; color: var(--muted); text-align: center; font-size: 10px; }
    .order-layout { display: grid; grid-template-columns: minmax(0, 1fr) 330px; align-items: start; gap: 23px; margin-top: 23px; }
    .order-content-card { padding: 24px; }
    .order-content-card h2 { margin: 0 0 18px; font-size: 17px; }
    .order-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 15px; padding: 14px 0; border-bottom: 1px solid var(--border); }
    .order-item:last-child { border-bottom: 0; }
    .order-item strong { font-size: 13px; }
    .order-item span { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; }
    .order-item > strong { color: var(--primary); }
    .detail-row { display: flex; justify-content: space-between; gap: 20px; margin: 13px 0; color: var(--muted); font-size: 12px; }
    .detail-row strong { color: var(--text); text-align: right; }
    .detail-address { margin: 18px 0 0; padding: 15px; border-radius: 9px; background: #f7f7f8; color: var(--muted); font-size: 11px; line-height: 1.65; }
    .values-card { margin-top: 17px; padding-top: 16px; border-top: 1px solid var(--border); }
    .total-row { font-size: 15px; font-weight: 850; }
    .total-row strong { color: var(--primary); font-size: 19px; }
    .back-link { min-height: 43px; display: grid; place-items: center; margin-top: 15px; border: 1px solid var(--border); border-radius: 9px; background: white; color: var(--text); font-size: 12px; font-weight: 800; text-decoration: none; }
    @media (max-width: 800px) { .order-layout { grid-template-columns: 1fr; } }
    @media (max-width: 620px) { .order-heading { flex-direction: column; } .status-card { padding: 22px 12px; overflow-x: auto; } .status-track { min-width: 570px; } .order-content-card { padding: 21px 17px; } }
</style>
@endpush

@section('content')
    @php
        $statuses = [
            'novo' => ['label' => 'Novo', 'description' => 'Pedido recebido'],
            'em_preparo' => ['label' => 'Em Preparo', 'description' => 'Sendo preparado'],
            'saiu_para_entrega' => ['label' => 'Saiu para Entrega', 'description' => 'A caminho'],
            'concluido' => ['label' => 'Concluído', 'description' => 'Pedido finalizado'],
        ];
        $statusKeys = array_keys($statuses);
        $currentIndex = array_search($order->status, $statusKeys, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $currentStatus = $statuses[$order->status] ?? ['label' => ucfirst($order->status), 'description' => 'Status atual'];
        $address = $order->delivery_address_snapshot;
    @endphp

    <section class="order-page container" data-current-status="{{ $order->status }}">
        <header class="order-heading">
            <div>
                <h1>Pedido #{{ $order->order_number }}</h1>
                <p>Realizado em {{ $order->created_at->format('d/m/Y') }} às {{ $order->created_at->format('H:i') }}</p>
            </div>
            <span class="current-status">{{ $currentStatus['label'] }}</span>
        </header>

        <section class="status-card" aria-labelledby="status-title">
            <h2 id="status-title">Acompanhe seu pedido</h2>
            <div class="status-track">
                @foreach ($statuses as $status => $content)
                    @php $index = array_search($status, $statusKeys, true); @endphp
                    <div class="status-step {{ $index < $currentIndex ? 'complete' : ($index === $currentIndex ? 'current' : '') }}">
                        <span class="status-point" aria-hidden="true">{{ $index <= $currentIndex ? '✓' : $index + 1 }}</span>
                        <strong>{{ $content['label'] }}</strong>
                        <span>{{ $content['description'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="refresh-note">Recarregue esta página para consultar uma atualização do status.</p>
        </section>

        <div class="order-layout">
            <section class="order-content-card" aria-labelledby="items-title">
                <h2 id="items-title">Itens do Pedido</h2>
                @foreach ($order->items as $item)
                    <article class="order-item">
                        <div><strong>{{ $item->product_name_snapshot }}</strong><span>{{ $item->quantity }} × R$ {{ number_format((float) $item->unit_price_snapshot, 2, ',', '.') }}</span></div>
                        <strong>R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</strong>
                    </article>
                @endforeach
            </section>

            <aside>
                <section class="order-content-card" aria-labelledby="details-title">
                    <h2 id="details-title">Detalhes do Pedido</h2>
                    <div class="detail-row"><span>Pagamento</span><strong>{{ $order->payment_method === 'pix' ? 'Pix' : 'Cartão de crédito' }}</strong></div>
                    <div class="detail-row"><span>Status do pagamento</span><strong>{{ $order->payment_status === 'pago' ? 'Pago' : ucfirst($order->payment_status) }}</strong></div>
                    <div class="detail-row"><span>Recebimento</span><strong>{{ $order->delivery_method === 'entrega' ? 'Entrega' : 'Retirada no local' }}</strong></div>

                    <div class="detail-address">
                        @if ($address)
                            <strong>Endereço de entrega</strong><br>
                            {{ $address['street'] }}, {{ $address['number'] }}<br>
                            {{ $address['neighborhood'] }} — {{ $address['city'] }}@if ($address['complement'] ?? null)<br>{{ $address['complement'] }}@endif
                        @else
                            <strong>Retirada no local</strong><br>Apresente o número do pedido ao retirar.
                        @endif
                    </div>

                    <div class="values-card">
                        <div class="detail-row"><span>Subtotal</span><strong>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</strong></div>
                        <div class="detail-row"><span>Taxa de entrega</span><strong>R$ {{ number_format((float) $order->delivery_fee, 2, ',', '.') }}</strong></div>
                        <div class="detail-row total-row"><span>Total</span><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
                    </div>
                </section>
                <a class="back-link" href="{{ route('site.orders.index') }}">Voltar aos Meus Pedidos</a>
            </aside>
        </div>
    </section>
@endsection
