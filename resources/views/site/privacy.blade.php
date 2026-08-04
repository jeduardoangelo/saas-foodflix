@extends('site.layouts.app')

@section('title', 'Política de Privacidade')

@section('content')
    <main class="container" style="max-width: 850px; padding-top: 3rem; padding-bottom: 4rem;">
        <h1>Política de Privacidade</h1>
        <p>Esta política explica, de forma objetiva, como a Foodflix utiliza os dados necessários para prestar o serviço.</p>

        <h2>Dados coletados e finalidade</h2>
        <p>Coletamos nome, CPF e email para identificar o cliente e o pedido. O endereço é usado para realizar a entrega, e o telefone, quando informado, permite contato relacionado ao atendimento e à entrega.</p>

        <h2>Compartilhamento</h2>
        <p>Não compartilhamos dados pessoais com terceiros, exceto os dados estritamente necessários ao processamento do pagamento quando um gateway de pagamento for integrado. A Foodflix não armazena número de cartão nem código de segurança (CVV).</p>

        <h2>Proteção e direitos</h2>
        <p>Adotamos medidas para proteger os dados contra acesso indevido. O cliente pode solicitar informações, correção ou exclusão de seus dados entrando em contato com a loja, respeitadas as obrigações legais de conservação.</p>
    </main>
@endsection
