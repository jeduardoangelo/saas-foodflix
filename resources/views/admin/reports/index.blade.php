@extends('admin.layouts.app')

@section('title', 'Relatórios')

@push('styles')
<style>
    .report-actions { display:flex; flex-wrap:wrap; gap:9px; }
    .report-actions .button { min-height:39px; padding-inline:13px; font-size:12px; }
    .report-filters { display:flex; flex-wrap:wrap; align-items:end; gap:12px; margin-bottom:20px; padding:16px; border:1px solid var(--border); border-radius:11px; background:white; }
    .filter-field { display:grid; gap:6px; min-width:155px; }
    .filter-field label { color:var(--muted); font-size:11px; font-weight:700; }
    .filter-field input { min-height:39px; padding:0 10px; border:1px solid var(--border); border-radius:7px; background:white; color:var(--text); }
    .filter-actions { display:flex; gap:8px; }
    .filter-actions .button { min-height:39px; }
    .selected-customer { display:flex; align-items:center; gap:8px; margin:-7px 0 19px; color:var(--muted); font-size:12px; }
    .selected-customer strong { color:var(--text); }
    .selected-customer a { color:var(--primary); font-weight:700; text-decoration:none; }
    .report-summary { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:19px; }
    .report-metric { min-height:110px; padding:16px; border:1px solid var(--border); border-radius:11px; background:white; box-shadow:0 6px 20px rgba(31,41,55,.035); }
    .report-metric span { display:block; color:var(--muted); font-size:11px; font-weight:650; }
    .report-metric strong { display:block; margin-top:11px; font-size:21px; letter-spacing:-.45px; }
    .report-metric.money strong { color:var(--success); }
    .report-grid { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(260px,.85fr); gap:16px; margin-bottom:16px; }
    .report-card { min-width:0; overflow:hidden; border:1px solid var(--border); border-radius:11px; background:white; box-shadow:0 6px 20px rgba(31,41,55,.035); }
    .report-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:17px 18px; border-bottom:1px solid var(--border); }
    .report-card-header h2 { margin:0; font-size:14px; }
    .report-card-header p { margin:5px 0 0; color:var(--muted); font-size:11px; }
    .report-card-body { padding:16px 18px; }
    .trend-bars { display:grid; align-items:end; gap:5px; height:205px; padding:8px 1px 0; }
    .trend-item { display:flex; min-width:0; height:100%; flex-direction:column; align-items:center; justify-content:flex-end; gap:7px; }
    .trend-bar { display:flex; width:min(100%,24px); height:164px; align-items:flex-end; overflow:hidden; border-radius:5px 5px 2px 2px; background:#f3f4f6; }
    .trend-bar span { display:block; width:100%; min-height:0; border-radius:5px 5px 0 0; background:linear-gradient(180deg,#fb704c,var(--primary)); }
    .trend-item small { min-height:12px; color:var(--muted); font-size:9px; white-space:nowrap; }
    .chart-empty { display:grid; height:205px; place-items:center; color:var(--muted); font-size:12px; }
    .status-list { display:grid; gap:15px; }
    .status-top { display:flex; justify-content:space-between; gap:12px; margin-bottom:6px; font-size:11px; }
    .status-top span { color:var(--muted); }
    .status-top strong { font-size:11px; }
    .status-track { height:7px; overflow:hidden; border-radius:99px; background:#f0f1f3; }
    .status-track span { display:block; height:100%; min-width:0; border-radius:inherit; background:var(--primary); }
    .status-row[data-status="novo"] .status-track span { background:#ef4444; }
    .status-row[data-status="em_preparo"] .status-track span { background:#f59e0b; }
    .status-row[data-status="saiu_para_entrega"] .status-track span { background:#3b82f6; }
    .status-row[data-status="concluido"] .status-track span { background:#22c55e; }
    .status-row[data-status="cancelado"] .status-track span { background:#9ca3af; }
    .report-tables { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; margin-bottom:16px; }
    .table-wrap { width:100%; overflow:auto; }
    .report-table { width:100%; border-collapse:collapse; text-align:left; white-space:nowrap; }
    .report-table th { padding:11px 14px; border-bottom:1px solid var(--border); background:#fafafa; color:var(--muted); font-size:10px; font-weight:750; }
    .report-table td { padding:12px 14px; border-bottom:1px solid #f0f1f3; font-size:11px; }
    .report-table tr:last-child td { border-bottom:0; }
    .table-primary { display:block; color:var(--text); font-size:11px; font-weight:750; text-decoration:none; }
    .table-secondary { display:block; margin-top:4px; color:var(--muted); font-size:10px; }
    .history-link { color:var(--primary); font-weight:750; text-decoration:none; }
    .status-pill { display:inline-flex; padding:5px 8px; border-radius:99px; background:#f3f4f6; color:#4b5563; font-size:10px; font-weight:750; }
    .status-pill.novo { background:#fff1f2; color:#be123c; }
    .status-pill.em_preparo { background:#fffbeb; color:#b45309; }
    .status-pill.saiu_para_entrega { background:#eff6ff; color:#1d4ed8; }
    .status-pill.concluido { background:#f0fdf4; color:#15803d; }
    .pagination-wrap { padding:12px 14px; border-top:1px solid var(--border); color:var(--muted); font-size:11px; }
    .pagination-wrap nav { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .pagination-wrap nav > div:first-child { display:none; }
    .pagination-wrap nav > div:last-child > span, .pagination-wrap nav > div:last-child > a { display:inline-flex; gap:4px; }
    .pagination-wrap a, .pagination-wrap span[aria-current="page"] span, .pagination-wrap span[aria-disabled="true"] span { display:inline-flex; padding:6px 9px; border:1px solid var(--border); border-radius:6px; background:white; color:var(--text); text-decoration:none; }
    .pagination-wrap span[aria-current="page"] span { border-color:var(--primary); background:var(--primary); color:white; }
    .pagination-wrap span[aria-disabled="true"] span { color:#a1a1aa; }
    .table-empty { padding:22px 14px; color:var(--muted); text-align:center; font-size:12px; }
    @media (max-width:1100px) { .report-summary { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:800px) { .report-grid,.report-tables { grid-template-columns:1fr; } }
    @media (max-width:600px) { .report-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } .report-metric { min-height:96px; padding:13px; } .report-metric strong { font-size:18px; } .report-card-header { padding:15px; } .report-card-body { padding:14px; } }
</style>
@endpush

@section('content')
    @php
        $statusLabels = ['novo' => 'Novo', 'em_preparo' => 'Em preparo', 'saiu_para_entrega' => 'Saiu para entrega', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];
        $paymentLabels = ['pago' => 'Pago', 'pendente' => 'Pendente', 'falhou' => 'Falhou', 'estornado' => 'Estornado'];
        $trendCount = max(1, count($salesTrend['points']));
    @endphp

    <div class="page-heading">
        <div><h1>Relatórios</h1><p>Acompanhe vendas, pedidos e clientes no período selecionado.</p></div>
        <div class="report-actions">
            <a class="button button-secondary" href="{{ route('admin.reports.orders.export', $filters) }}">Exportar pedidos (.xlsx)</a>
            <a class="button button-primary" href="{{ route('admin.reports.customers.export', $filters) }}">Exportar clientes (.xlsx)</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="report-filters" method="GET" action="{{ route('admin.reports.index') }}">
        <div class="filter-field"><label for="start_date">De</label><input id="start_date" type="date" name="start_date" value="{{ $filters['start_date'] }}" required></div>
        <div class="filter-field"><label for="end_date">Até</label><input id="end_date" type="date" name="end_date" value="{{ $filters['end_date'] }}" required></div>
        @if ($selectedCustomer)
            <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
        @endif
        <div class="filter-actions"><button class="button button-primary" type="submit">Aplicar período</button></div>
    </form>

    @if ($selectedCustomer)
        <div class="selected-customer"><span>Histórico de:</span><strong>{{ $selectedCustomer->name }}</strong><a href="{{ route('admin.reports.index', ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}">Ver todos os clientes</a></div>
    @endif

    <section class="report-summary" aria-label="Indicadores do período">
        <article class="report-metric money"><span>Vendas pagas hoje</span><strong>R$ {{ number_format($metrics['today_sales'], 2, ',', '.') }}</strong></article>
        <article class="report-metric money"><span>Vendas pagas no período</span><strong>R$ {{ number_format($metrics['period_sales'], 2, ',', '.') }}</strong></article>
        <article class="report-metric"><span>Pedidos no período</span><strong>{{ number_format($metrics['order_count'], 0, ',', '.') }}</strong></article>
        <article class="report-metric"><span>Ticket médio pago</span><strong>R$ {{ number_format($metrics['average_ticket'], 2, ',', '.') }}</strong></article>
        <article class="report-metric"><span>Clientes com pedidos</span><strong>{{ number_format($metrics['customer_count'], 0, ',', '.') }}</strong></article>
    </section>

    <section class="report-grid">
        <article class="report-card">
            <header class="report-card-header"><div><h2>Vendas ao longo do período</h2><p>{{ $salesTrend['monthly'] ? 'Agrupadas por mês' : 'Agrupadas por dia' }} · pedidos pagos</p></div></header>
            <div class="report-card-body">
                @if ($salesTrend['total'] > 0)
                    <div class="trend-bars" style="grid-template-columns:repeat({{ $trendCount }},minmax(3px,1fr))" role="img" aria-label="Gráfico de vendas pagas por {{ $salesTrend['monthly'] ? 'mês' : 'dia' }}">
                        @foreach ($salesTrend['points'] as $point)
                            <div class="trend-item" title="{{ $point['label'] }} — R$ {{ number_format($point['value'], 2, ',', '.') }}">
                                <div class="trend-bar"><span style="height:{{ $point['percent'] }}%"></span></div>
                                <small>@if ($salesTrend['monthly'] || $point['show_label']){{ $point['label'] }}@else&nbsp;@endif</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="chart-empty">Não há vendas pagas nesse período.</div>
                @endif
            </div>
        </article>

        <article class="report-card">
            <header class="report-card-header"><div><h2>Pedidos por status</h2><p>Distribuição dos pedidos no período</p></div></header>
            <div class="report-card-body">
                @if ($metrics['order_count'] > 0)
                    <div class="status-list">
                        @foreach ($statuses as $status)
                            <div class="status-row" data-status="{{ $status['key'] }}">
                                <div class="status-top"><span>{{ $status['label'] }}</span><strong>{{ $status['count'] }} ({{ $status['percent'] }}%)</strong></div>
                                <div class="status-track"><span style="width:{{ $status['percent'] }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="chart-empty">Não há pedidos nesse período.</div>
                @endif
            </div>
        </article>
    </section>

    <section class="report-tables">
        <article class="report-card">
            <header class="report-card-header"><div><h2>Produtos mais vendidos</h2><p>Ordenados pela quantidade vendida</p></div></header>
            <div class="table-wrap"><table class="report-table"><thead><tr><th>Produto</th><th>Unidades</th><th>Vendas</th></tr></thead><tbody>
                @forelse ($topProducts as $product)
                    <tr><td><strong>{{ $product->name }}</strong></td><td>{{ $product->quantity_sold }}</td><td>R$ {{ number_format((float) $product->sales_total, 2, ',', '.') }}</td></tr>
                @empty
                    <tr><td class="table-empty" colspan="3">Não há itens vendidos nesse período.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>

        <article class="report-card">
            <header class="report-card-header"><div><h2>Clientes do período</h2><p>Selecione um cliente para ver seu histórico de pedidos</p></div></header>
            <div class="table-wrap"><table class="report-table"><thead><tr><th>Cliente</th><th>Pedidos</th><th>Total pago</th><th></th></tr></thead><tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td><strong class="table-primary">{{ $customer->name }}</strong><span class="table-secondary">{{ $customer->email }}</span></td>
                        <td>{{ $customer->orders_count }}</td>
                        <td>R$ {{ number_format((float) $customer->paid_total, 2, ',', '.') }}</td>
                        <td><a class="history-link" href="{{ route('admin.reports.index', ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date'], 'customer_id' => $customer->id]) }}#orders">Histórico</a></td>
                    </tr>
                @empty
                    <tr><td class="table-empty" colspan="4">Nenhum cliente fez pedidos nesse período.</td></tr>
                @endforelse
            </tbody></table></div>
            @if ($customers->hasPages())<div class="pagination-wrap">{{ $customers->links() }}</div>@endif
        </article>
    </section>

    <article class="report-card" id="orders">
        <header class="report-card-header"><div><h2>{{ $selectedCustomer ? 'Histórico de pedidos' : 'Pedidos do período' }}</h2><p>{{ $selectedCustomer ? 'Pedidos de '.$selectedCustomer->name : 'Pedidos mais recentes primeiro' }}</p></div></header>
        <div class="table-wrap"><table class="report-table"><thead><tr><th>Pedido</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th>Recebimento</th><th>Status</th><th>Total</th></tr></thead><tbody>
            @forelse ($orders as $order)
                <tr>
                    <td><strong>#{{ $order->order_number }}</strong></td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($order->user)->name ?: 'Cliente removido' }}</td>
                    <td>{{ $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}</td>
                    <td>{{ $order->delivery_method === 'retirada' ? 'Retirada' : 'Entrega' }}</td>
                    <td><span class="status-pill {{ $order->status }}">{{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                    <td><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr><td class="table-empty" colspan="7">Nenhum pedido encontrado para os filtros selecionados.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($orders->hasPages())<div class="pagination-wrap">{{ $orders->links() }}</div>@endif
    </article>
@endsection
