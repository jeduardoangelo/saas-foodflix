@extends('site.layouts.app')

@section('title', 'Meus Pedidos')

@push('styles')
<style>
    .orders-page { padding: 42px 0 18px; }
    .orders-heading { margin-bottom: 24px; }
    .orders-heading h1 { margin: 0 0 7px; font-size: clamp(28px,4vw,37px); letter-spacing: -.8px; }
    .orders-heading p { margin: 0; color: var(--muted); font-size: 13px; }
    .orders-list { display: grid; gap: 12px; }
    .history-order { display: grid; grid-template-columns: minmax(0,1.4fr) minmax(150px,.7fr) minmax(125px,.55fr) auto; align-items: center; gap: 20px; padding: 19px 21px; border: 1px solid var(--border); border-radius: 13px; background: #fff; color: inherit; text-decoration: none; box-shadow: 0 7px 24px rgba(31,41,55,.04); }
    .history-order:hover { border-color: rgba(240,78,35,.35); box-shadow: 0 10px 28px rgba(31,41,55,.08); }
    .history-order strong { display: block; font-size: 14px; }
    .history-order small { display: block; margin-top: 5px; color: var(--muted); font-size: 11px; }
    .history-status { justify-self: start; padding: 7px 11px; border-radius: 999px; background: var(--primary-soft); color: var(--primary); font-size: 10px; font-weight: 850; }
    .history-total { color: var(--primary); font-size: 14px; font-weight: 850; white-space: nowrap; }
    .history-action { color: var(--primary); font-size: 12px; font-weight: 800; }
    .orders-empty { padding: 55px 25px; border: 1px solid var(--border); border-radius: 14px; background: #fff; text-align: center; }
    .orders-empty h2 { margin: 0 0 8px; font-size: 19px; }
    .orders-empty p { margin: 0 0 20px; color: var(--muted); font-size: 13px; }
    .orders-empty a { display: inline-grid; min-height: 41px; place-items: center; padding: 0 18px; border-radius: 8px; background: var(--cta); color: #fff; font-size: 12px; font-weight: 800; text-decoration: none; }
    .pagination { display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 24px; color: var(--muted); font-size: 12px; }
    .pagination a { padding: 9px 13px; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--primary); font-weight: 750; text-decoration: none; }
    @media (max-width:700px) { .history-order { grid-template-columns: 1fr auto; gap: 12px; } .history-status { justify-self: end; } .history-action { display:none; } }
</style>
@endpush

@section('content')
    @php
        $statusLabels = ['novo' => 'Novo', 'em_preparo' => 'Em Preparo', 'saiu_para_entrega' => 'Saiu para Entrega', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];
    @endphp
    <section class="orders-page container">
        <header class="orders-heading"><h1>Meus Pedidos</h1><p>Acompanhe pedidos atuais e consulte seu histórico.</p></header>

        @if ($orders->isEmpty())
            <div class="orders-empty"><h2>Você ainda não fez nenhum pedido</h2><p>Escolha seus produtos favoritos no cardápio para começar.</p><a href="{{ route('site.home') }}">Ver Cardápio</a></div>
        @else
            <div class="orders-list">
                @foreach ($orders as $order)
                    <a class="history-order" href="{{ route('site.orders.show', $order) }}">
                        <div><strong>Pedido #{{ $order->order_number }}</strong><small>{{ $order->created_at->format('d/m/Y \à\s H:i') }}</small></div>
                        <span class="history-status">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                        <span class="history-total">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                        <span class="history-action">Ver detalhes →</span>
                    </a>
                @endforeach
            </div>

            @if ($orders->hasPages())
                <nav class="pagination" aria-label="Paginação dos pedidos">
                    @if ($orders->onFirstPage()) <span>Anterior</span> @else <a href="{{ $orders->previousPageUrl() }}">Anterior</a> @endif
                    <span>Página {{ $orders->currentPage() }} de {{ $orders->lastPage() }}</span>
                    @if ($orders->hasMorePages()) <a href="{{ $orders->nextPageUrl() }}">Próxima</a> @else <span>Próxima</span> @endif
                </nav>
            @endif
        @endif
    </section>
@endsection
