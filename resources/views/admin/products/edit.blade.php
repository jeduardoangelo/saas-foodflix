@extends('admin.layouts.app')

@section('title', 'Editar Produto')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Editar Produto</h1>
            <p>Atualize as informações de {{ $product->name }}.</p>
        </div>
    </div>

    @include('admin.products._form', [
        'action' => route('admin.products.update', $product),
        'method' => 'PUT',
        'submitLabel' => 'Salvar Alterações',
    ])
@endsection
