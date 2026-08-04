@extends('admin.layouts.app')

@section('title', 'Produtos')

@push('styles')
<style>
    .table-wrap { overflow-x: auto; }
    .product-table { width: 100%; border-collapse: collapse; min-width: 800px; }
    .product-table th { padding: 16px 18px; border-bottom: 1px solid var(--border); color: var(--muted); font-size: 12px; font-weight: 650; text-align: left; }
    .product-table td { padding: 15px 18px; border-bottom: 1px solid #f0f1f3; font-size: 13px; vertical-align: middle; }
    .product-table tbody tr:last-child td { border-bottom: 0; }
    .product-table tbody tr:hover { background: #fdfdfd; }
    .product-image { width: 54px; height: 54px; display: grid; place-items: center; overflow: hidden; border-radius: 9px; background: var(--input); color: #a0a6af; }
    .product-image img { width: 100%; height: 100%; object-fit: cover; }
    .product-name { font-weight: 700; }
    .category-name { color: var(--muted); }
    .price { color: var(--primary); font-weight: 750; white-space: nowrap; }
    .status-form, .action-form { display: inline; margin: 0; }
    .status-toggle { width: 42px; height: 23px; position: relative; padding: 0; border: 0; border-radius: 999px; background: #d1d5db; cursor: pointer; vertical-align: middle; transition: background .2s; }
    .status-toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 17px; height: 17px; border-radius: 50%; background: white; box-shadow: 0 1px 4px rgba(0,0,0,.2); transition: transform .2s; }
    .status-toggle.active { background: #1f2937; }
    .status-toggle.active::after { transform: translateX(19px); }
    .status-label { margin-left: 8px; color: var(--muted); font-size: 12px; }
    .actions { display: flex; align-items: center; gap: 6px; }
    .icon-button { width: 35px; height: 35px; display: inline-grid; place-items: center; padding: 0; border: 0; border-radius: 8px; background: transparent; cursor: pointer; text-decoration: none; }
    .icon-button svg { width: 18px; height: 18px; }
    .icon-button.edit { color: #2563eb; }
    .icon-button.delete { color: var(--danger); }
    .icon-button:hover { background: #f3f4f6; }
    .empty-state { padding: 56px 24px; text-align: center; color: var(--muted); }
    .pagination { display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding: 16px 18px; border-top: 1px solid var(--border); color: var(--muted); font-size: 12px; }
    .pagination a { padding: 7px 11px; border: 1px solid var(--border); border-radius: 7px; background: white; text-decoration: none; }
</style>
@endpush

@section('content')
    <div class="page-heading">
        <div>
            <h1>Gerenciamento de Produtos</h1>
            <p>Gerencie o catálogo de produtos do Foodflix.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.products.create') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
            Adicionar Novo Produto
        </a>
    </div>

    <section class="card">
        @if ($products->isEmpty())
            <div class="empty-state">
                <strong>Nenhum produto cadastrado.</strong>
                <p>Adicione o primeiro item para começar a montar o catálogo.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="product-image">
                                        @if ($product->image_url)
                                            <img src="{{ asset('storage/'.$product->image_url) }}" alt="Imagem de {{ $product->name }}">
                                        @else
                                            <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path></svg>
                                        @endif
                                    </div>
                                </td>
                                <td class="product-name">{{ $product->name }}</td>
                                <td class="category-name">{{ $product->category->name }}</td>
                                <td class="price">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                                <td>
                                    <form class="status-form" method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="status-toggle {{ $product->active ? 'active' : '' }}" type="submit" aria-label="{{ $product->active ? 'Desativar' : 'Ativar' }} {{ $product->name }}" aria-pressed="{{ $product->active ? 'true' : 'false' }}"></button>
                                        <span class="status-label">{{ $product->active ? 'Ativo' : 'Inativo' }}</span>
                                    </form>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="icon-button edit" href="{{ route('admin.products.edit', $product) }}" aria-label="Editar {{ $product->name }}" title="Editar">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                                        </a>
                                        <form class="action-form" method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Deseja excluir este produto? Esta ação o removerá do catálogo.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button delete" type="submit" aria-label="Excluir {{ $product->name }}" title="Excluir">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 14H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <nav class="pagination" aria-label="Paginação de produtos">
                    @if ($products->onFirstPage())
                        <span>Anterior</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}">Anterior</a>
                    @endif
                    <span>Página {{ $products->currentPage() }} de {{ $products->lastPage() }}</span>
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}">Próxima</a>
                    @else
                        <span>Próxima</span>
                    @endif
                </nav>
            @endif
        @endif
    </section>
@endsection
