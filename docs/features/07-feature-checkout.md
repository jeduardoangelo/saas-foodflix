# Feature: Checkout (Finalizar Pedido)

> Documento 7 de N. Parte do MVP (documento 1). Inclui pagamento online via gateway (decisão registrada no documento 1).

## Objetivo
Converter o carrinho em um pedido confirmado e pago, com o mínimo de fricção e máxima transparência (princípios de UX, documento 1).

## Papéis envolvidos
- Cliente Final (persona 1, documento 1) — autenticado (login obrigatório neste ponto)

## Fluxo principal
1. Cliente autenticado avança do carrinho para "Finalizar Pedido"
2. Escolhe forma de recebimento: **Entrega** ou **Retirar no Local**
3. Se "Entrega": preenche/confirma endereço (pré-preenchido com o endereço padrão do cadastro, editável)
4. Seleciona forma de pagamento (integração com gateway — cartão de crédito, Pix, conforme o gateway escolhido em `payments.method`, documento 2)
5. Revisa resumo do pedido (itens, subtotal, taxa de entrega, total)
6. Confirma o pedido → sistema cria registro em `orders` + `order_items` (com snapshots, documento 2) e inicia a cobrança no gateway
7. Após confirmação do gateway, `payment_status` é atualizado e o pedido entra na fila do admin como "novo"

## Telas
- `site-finalizar-pedido.png` — Finalizar Pedido (Como você quer receber, Endereço de Entrega, Forma de Pagamento, Resumo do Pedido)

## Regras de negócio
- Login é obrigatório para acessar o checkout (se o cliente não estiver logado, é redirecionado ao login/cadastro antes)
- Se "Retirar no Local" for selecionado, o formulário de endereço é ocultado e `delivery_address_snapshot` fica nulo (documento 2); `delivery_fee` do pedido é zero
- Se "Entrega" for selecionado, `delivery_fee` do pedido é copiado de `store_settings.delivery_fee` no momento da confirmação
- Pedido só é criado em `orders` **após** confirmação de que a cobrança foi iniciada com sucesso no gateway — evitar criar pedidos "fantasma" sem tentativa de pagamento correspondente
- `payment_status = 'pago'` só é definido mediante confirmação do gateway (webhook ou callback síncrono, a detalhar na implementação do `Services/Payment`, documento 3); nunca marcar como pago apenas pela submissão do formulário
- Falha no pagamento mantém o carrinho intacto e permite nova tentativa, sem duplicar o pedido

## Critérios de aceite
- [ ] Cliente não logado é direcionado a login/cadastro antes de acessar o checkout
- [ ] Alternar entre "Entrega" e "Retirar no Local" atualiza corretamente a exibição do formulário de endereço e o total (com/sem taxa)
- [ ] Resumo do pedido reflete exatamente os itens e valores do carrinho
- [ ] Confirmação do pedido só ocorre após retorno de sucesso do gateway de pagamento
- [ ] Pedido criado aparece imediatamente na coluna "Novos Pedidos" do Kanban do admin
- [ ] Falha de pagamento exibe mensagem clara e não gera pedido órfão sem cobrança associada

## Fora de escopo
- Múltiplos métodos de pagamento simultâneos em um mesmo pedido (split de pagamento)
- Pagamento na entrega (dinheiro/cartão físico) — modelo de dados comporta (`payments.method`), mas o MVP prioriza pagamento online conforme decisão do documento 1; incluir dinheiro/cartão físico como opção é uma extensão simples caso o negócio precise, mas não está no MVP inicial
- Aplicação de cupom de desconto (Fase 3, documento 1)
