@extends('admin.layouts.app')

@section('title', 'Adicionar Produto')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Adicionar Novo Produto</h1>
            <p>Cadastre um novo item no catálogo do Foodflix.</p>
        </div>
    </div>

    @include('admin.products._form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'submitLabel' => 'Cadastrar Produto',
    ])
@endsection
