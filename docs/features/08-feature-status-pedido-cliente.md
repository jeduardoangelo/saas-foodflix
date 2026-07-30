# Feature: Acompanhamento de Status do Pedido (Cliente)

> Documento 8 de N. Parte do MVP (documento 1).

## Objetivo
Dar transparência ao cliente sobre o andamento do pedido já feito, reduzindo incerteza e mensagens de suporte do tipo "cadê meu pedido?".

## Papéis envolvidos
- Cliente Final (persona 1, documento 1)

## Fluxo principal
1. Cliente acessa a tela de detalhe do pedido (a partir de confirmação do checkout, ou de uma futura listagem "Meus Pedidos")
2. Sistema exibe dados do pedido: número, itens, valores, forma de pagamento, método de entrega e **status atual**
3. Status reflete o mesmo valor controlado pelo admin no Kanban (`orders.status`, documento 2): Novo, Em Preparo, Saiu para Entrega, Concluído

## Telas
- `site-detalhes-pedido.png` — Detalhes do Pedido (visão do cliente)

## Regras de negócio
- Status exibido é sempre o valor atual de `orders.status` — sem cache desatualizado
- Cliente só pode ver pedidos vinculados ao seu próprio `user_id` (autorização via Policy, documento 3)
- Não há atualização automática em tempo real (websocket) no MVP — cliente precisa recarregar a página para ver mudança de status

## Critérios de aceite
- [ ] Tela exibe corretamente todos os dados do pedido (itens, valores, endereço/retirada, pagamento)
- [ ] Status exibido corresponde exatamente ao status atual no admin
- [ ] Cliente não consegue acessar detalhes de pedido de outro cliente (testar acesso direto por URL/ID)

## Fora de escopo
- Atualização em tempo real via websocket/polling automático — Fase 2 do roadmap junto com notificações (documento 1)
- Listagem "Meus Pedidos" (histórico completo) — não estava nos prints fornecidos; se necessária no MVP, avisar para adicionar como tela adicional
- Rastreamento de entrega em mapa (localização do entregador)
