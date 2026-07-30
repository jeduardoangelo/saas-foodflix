# Feature: Dashboard e Kanban de Pedidos (Admin)

> Documento 10 de N. Parte do MVP (documento 1). Esta é a feature operacional central do sistema — uso diário, sob pressão de horário de pico (princípio de UX: eficiência operacional, documento 1).

## Objetivo
Dar ao administrador visão em tempo real de todos os pedidos e permitir avançar cada um pelo fluxo de preparo/entrega com o mínimo de cliques.

## Papéis envolvidos
- Administrador/Operador (persona 2, documento 1)

## Fluxo principal
1. Administrador acessa o Dashboard após login
2. Vê cards de resumo: Novos Pedidos, Faturamento do Dia, Pedidos Concluídos, Em Andamento
3. Vê o Kanban com 4 colunas: **Novos Pedidos → Em Preparo → Saiu para Entrega → Concluídos**, cada uma com contador
4. Cada card de pedido mostra: número, horário, nome do cliente, valor total, itens resumidos
5. Administrador clica "Ver Detalhes" para abrir modal com dados completos do cliente, itens, valores e forma de pagamento/entrega
6. Administrador avança o pedido para a próxima coluna (ação de mudança de status — mecanismo exato, drag-and-drop ou botão, a definir na implementação visual)

## Telas
- `admin-dashboard.png` — Dashboard com cards de resumo e Kanban
- `admin-detalhes-pedido.png` — Modal "Detalhes do Pedido" (dados do cliente, resumo, itens, valores)

## Regras de negócio
- Contadores dos cards de resumo ("Novos Pedidos: 2", "Faturamento do Dia: R$ 370.10") são calculados a partir de `orders` filtrados por status e data — sempre em tempo real, sem cache manual desatualizado
- "Faturamento do Dia" soma `total` de pedidos com `payment_status = 'pago'` criados no dia corrente
- Transição de status segue sempre a ordem: `novo` → `em_preparo` → `saiu_para_entrega` → `concluido` (sem pular etapas) — exceto cancelamento, que pode ocorrer a partir de `novo` ou `em_preparo` (regra a confirmar; não há tela de cancelamento nos prints fornecidos)
- Modal de detalhes exibe o `delivery_address_snapshot` do pedido (não o endereço atual do cliente, que pode ter mudado desde então) — consistente com a regra de imutabilidade definida no documento 2

## Critérios de aceite
- [ ] Kanban exibe todos os pedidos do dia corretamente distribuídos por status
- [ ] Contadores dos cards de resumo batem com a contagem real de pedidos em cada status
- [ ] Avançar um pedido de status atualiza o Kanban sem necessidade de recarregar a página
- [ ] Modal de detalhes exibe corretamente todos os dados do pedido, incluindo snapshot de endereço e itens
- [ ] "Faturamento do Dia" reflete apenas pedidos efetivamente pagos

## Fora de escopo
- Filtro/busca de pedidos por período diferente do dia atual (histórico de faturamento por mês, por exemplo) — considerar para Fase 2 (relatórios, documento 1)
- Cancelamento de pedido pelo cliente (via site) — não visto nos prints; cancelamento, se existir no MVP, é uma ação do admin
- Notificação sonora/visual de novo pedido em tempo real (Fase 2, documento 1)
