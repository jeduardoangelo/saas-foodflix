@extends('admin.layouts.app')

@section('title', 'Pedidos')

@push('styles')
<style>
    .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; margin-bottom: 25px; }
    .summary-card { padding: 19px; border: 1px solid var(--border); border-radius: 11px; background: white; box-shadow: 0 6px 20px rgba(31,41,55,.04); }
    .summary-card span { display: block; color: var(--muted); font-size: 11px; font-weight: 650; }
    .summary-card strong { display: block; margin-top: 8px; font-size: 23px; letter-spacing: -.5px; }
    .summary-card.revenue strong { color: var(--success); }
    .kanban { display: grid; grid-template-columns: repeat(4, minmax(245px, 1fr)); align-items: start; gap: 15px; overflow-x: auto; padding-bottom: 10px; }
    .kanban-column { min-height: 360px; border: 1px solid var(--border); border-radius: 12px; background: #eef0f3; }
    .kanban-header { min-height: 53px; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0 14px; border-bottom: 1px solid rgba(31,41,55,.07); }
    .kanban-title { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; }
    [data-status="novo"] .status-dot { background: #ef4444; }
    [data-status="em_preparo"] .status-dot { background: #f59e0b; }
    [data-status="saiu_para_entrega"] .status-dot { background: #3b82f6; }
    [data-status="concluido"] .status-dot { background: #22c55e; }
    .column-count { min-width: 24px; height: 24px; display: grid; place-items: center; padding: 0 6px; border-radius: 999px; background: white; color: var(--muted); font-size: 10px; font-weight: 800; }
    .kanban-orders { display: grid; gap: 11px; padding: 11px; }
    .order-card { padding: 14px; border: 1px solid var(--border); border-radius: 9px; background: white; box-shadow: 0 5px 13px rgba(31,41,55,.05); }
    .order-card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .order-card-top strong { font-size: 13px; }
    .order-card-top time { color: var(--muted); font-size: 10px; }
    .customer-name { margin: 11px 0 5px; font-size: 12px; font-weight: 700; }
    .items-summary { min-height: 31px; margin: 0; color: var(--muted); font-size: 10px; line-height: 1.5; }
    .order-value { display: flex; justify-content: space-between; margin-top: 11px; padding-top: 10px; border-top: 1px solid var(--border); font-size: 11px; }
    .order-value strong { color: var(--primary); font-size: 13px; }
    .order-actions { display: grid; gap: 7px; margin-top: 12px; }
    .details-button, .advance-button { min-height: 34px; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 800; }
    .details-button { border: 1px solid var(--border); background: white; color: var(--text); }
    .advance-button { width: 100%; border: 0; background: var(--primary); color: white; }
    .advance-button:hover { background: #d93f19; }
    .advance-button:disabled { opacity: .6; cursor: wait; }
    .column-empty { padding: 25px 10px; color: #989ea8; text-align: center; font-size: 11px; }
    .order-dialog { width: min(92vw, 680px); max-height: 88vh; padding: 0; overflow: auto; border: 0; border-radius: 14px; color: var(--text); box-shadow: 0 25px 70px rgba(0,0,0,.28); }
    .order-dialog::backdrop { background: rgba(17,24,39,.6); backdrop-filter: blur(2px); }
    .modal-header { position: sticky; top: 0; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 19px 22px; border-bottom: 1px solid var(--border); background: white; }
    .modal-header h2 { margin: 0; font-size: 18px; }
    .modal-close { width: 32px; height: 32px; border: 0; border-radius: 7px; background: var(--input); cursor: pointer; font-size: 19px; }
    .modal-body { padding: 22px; }
    .modal-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .detail-section { padding: 16px; border: 1px solid var(--border); border-radius: 9px; }
    .detail-section.full { grid-column: 1 / -1; }
    .detail-section h3 { margin: 0 0 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .detail-line { display: flex; justify-content: space-between; gap: 18px; margin: 8px 0; color: var(--muted); font-size: 11px; }
    .detail-line strong { color: var(--text); text-align: right; }
    .address-snapshot { margin: 11px 0 0; padding-top: 11px; border-top: 1px solid var(--border); color: var(--muted); font-size: 11px; line-height: 1.6; }
    .modal-item { display: grid; grid-template-columns: 1fr auto; gap: 15px; padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 11px; }
    .modal-item:last-child { border-bottom: 0; }
    .modal-item span { display: block; margin-top: 3px; color: var(--muted); font-size: 10px; }
    .modal-total { font-size: 13px; font-weight: 850; }
    @media (max-width: 1100px) { .summary-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 650px) { .summary-grid { grid-template-columns: 1fr; } .modal-grid { grid-template-columns: 1fr; } .detail-section.full { grid-column: auto; } }
</style>
@endpush

@section('content')
    <div class="page-heading">
        <div><h1>Dashboard de Pedidos</h1><p>Login realizado com sucesso. Acompanhe a operação de hoje em tempo real.</p></div>
        <span style="color: var(--muted); font-size: 12px;">{{ now()->format('d/m/Y') }}</span>
    </div>

    @if ($errors->has('order'))<div class="alert alert-error" role="alert">{{ $errors->first('order') }}</div>@endif

    <section class="summary-grid" aria-label="Resumo dos pedidos de hoje">
        <article class="summary-card"><span>Novos Pedidos</span><strong data-metric="new_orders">{{ $metrics['new_orders'] }}</strong></article>
        <article class="summary-card revenue"><span>Faturamento do Dia</span><strong data-metric="revenue_cents">R$ {{ number_format($metrics['revenue_cents'] / 100, 2, ',', '.') }}</strong></article>
        <article class="summary-card"><span>Pedidos Concluídos</span><strong data-metric="completed_orders">{{ $metrics['completed_orders'] }}</strong></article>
        <article class="summary-card"><span>Em Andamento</span><strong data-metric="in_progress_orders">{{ $metrics['in_progress_orders'] }}</strong></article>
    </section>

    <section class="kanban" aria-label="Kanban de pedidos do dia">
        @foreach ($columns as $column)
            <div class="kanban-column" data-status="{{ $column['status'] }}">
                <header class="kanban-header"><div class="kanban-title"><span class="status-dot"></span>{{ $column['label'] }}</div><span class="column-count" data-column-count>{{ $column['count'] }}</span></header>
                <div class="kanban-orders" data-column-orders>
                    <p class="column-empty" data-column-empty @if ($column['count'] > 0) hidden @endif>Nenhum pedido nesta etapa.</p>
                    @foreach ($column['orders'] as $order)
                        @php
                            $nextStatuses = ['novo' => 'Em Preparo', 'em_preparo' => 'Saiu para Entrega', 'saiu_para_entrega' => 'Concluídos'];
                            $address = $order->delivery_address_snapshot;
                        @endphp
                        <article class="order-card" data-order-card="{{ $order->id }}">
                            <div class="order-card-top"><strong>#{{ $order->order_number }}</strong><time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('H:i') }}</time></div>
                            <p class="customer-name">{{ optional($order->user)->name ?: 'Cliente removido' }}</p>
                            <p class="items-summary">@foreach ($order->items->take(2) as $item){{ $item->quantity }}× {{ $item->product_name_snapshot }}@if (! $loop->last), @endif @endforeach @if ($order->items->count() > 2)+{{ $order->items->count() - 2 }} item(ns)@endif</p>
                            <div class="order-value"><span>Total</span><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
                            <div class="order-actions">
                                <button class="details-button" type="button" data-open-order="order-{{ $order->id }}">Ver Detalhes</button>
                                @if (isset($nextStatuses[$order->status]))
                                    <form method="POST" action="{{ route('admin.orders.advance', $order) }}" data-advance-form>@csrf @method('PATCH')<button class="advance-button" type="submit">Avançar para {{ $nextStatuses[$order->status] }}</button></form>
                                @endif
                            </div>

                            <dialog class="order-dialog" id="order-{{ $order->id }}">
                                <header class="modal-header"><h2>Detalhes do Pedido #{{ $order->order_number }}</h2><button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button></header>
                                <div class="modal-body"><div class="modal-grid">
                                    <section class="detail-section">
                                        <h3>Dados do Cliente</h3>
                                        <div class="detail-line"><span>Nome</span><strong>{{ optional($order->user)->name ?: 'Cliente removido' }}</strong></div>
                                        <div class="detail-line"><span>Email</span><strong>{{ optional($order->user)->email ?: 'Indisponível' }}</strong></div>
                                        <div class="detail-line"><span>CPF</span><strong>{{ optional($order->user)->cpf ?: 'Indisponível' }}</strong></div>
                                        @if (optional($order->user)->phone)<div class="detail-line"><span>Telefone</span><strong>{{ $order->user->phone }}</strong></div>@endif
                                        <p class="address-snapshot"><strong>Endereço no pedido:</strong><br>@if ($address){{ $address['street'] }}, {{ $address['number'] }} — {{ $address['neighborhood'] }}, {{ $address['city'] }}@if ($address['complement'] ?? null) — {{ $address['complement'] }}@endif @else Retirada no local @endif</p>
                                    </section>
                                    <section class="detail-section">
                                        <h3>Resumo do Pedido</h3>
                                        <div class="detail-line"><span>Data e hora</span><strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong></div>
                                        <div class="detail-line"><span>Recebimento</span><strong>{{ $order->delivery_method === 'entrega' ? 'Entrega' : 'Retirada' }}</strong></div>
                                        <div class="detail-line"><span>Pagamento</span><strong>{{ $order->payment_method === 'pix' ? 'Pix' : 'Cartão de crédito' }}</strong></div>
                                        <div class="detail-line"><span>Status do pagamento</span><strong>{{ ucfirst($order->payment_status) }}</strong></div>
                                    </section>
                                    <section class="detail-section full"><h3>Itens</h3>@foreach ($order->items as $item)<div class="modal-item"><div><strong>{{ $item->product_name_snapshot }}</strong><span>{{ $item->quantity }} × R$ {{ number_format((float) $item->unit_price_snapshot, 2, ',', '.') }}</span></div><strong>R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</strong></div>@endforeach</section>
                                    <section class="detail-section full"><div class="detail-line"><span>Subtotal</span><strong>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</strong></div><div class="detail-line"><span>Taxa de entrega</span><strong>R$ {{ number_format((float) $order->delivery_fee, 2, ',', '.') }}</strong></div><div class="detail-line modal-total"><span>Total</span><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div></section>
                                </div></div>
                            </dialog>
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        var money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        document.querySelectorAll('[data-open-order]').forEach(function (button) { button.addEventListener('click', function () { document.getElementById(button.dataset.openOrder).showModal(); }); });
        document.querySelectorAll('[data-close-modal]').forEach(function (button) { button.addEventListener('click', function () { button.closest('dialog').close(); }); });

        function refreshColumn(column) {
            var count = column.querySelectorAll('[data-order-card]').length;
            column.querySelector('[data-column-count]').textContent = count;
            column.querySelector('[data-column-empty]').hidden = count > 0;
        }

        document.querySelectorAll('[data-advance-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var button = form.querySelector('button');
                button.disabled = true;
                fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) { return response.json().then(function (data) { if (!response.ok) throw new Error(data.message || 'Não foi possível avançar o pedido.'); return data; }); })
                    .then(function (data) {
                        var card = document.querySelector('[data-order-card="' + data.order_id + '"]');
                        var sourceColumn = card.closest('[data-status]');
                        var targetColumn = document.querySelector('[data-status="' + data.status + '"]');
                        targetColumn.querySelector('[data-column-orders]').appendChild(card);
                        refreshColumn(sourceColumn); refreshColumn(targetColumn);
                        if (data.next_status) { button.textContent = 'Avançar para ' + data.next_label; button.disabled = false; } else { form.remove(); }
                        Object.keys(data.metrics).forEach(function (key) { var element = document.querySelector('[data-metric="' + key + '"]'); if (element) element.textContent = key === 'revenue_cents' ? money.format(data.metrics[key] / 100) : data.metrics[key]; });
                    })
                    .catch(function (error) { button.disabled = false; window.alert(error.message); });
            });
        });
    })();
</script>
@endpush
