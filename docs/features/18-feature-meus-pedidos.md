# Feature: Meus Pedidos (Histórico do Cliente)

> Documento 18 de N. Estende o documento 08 (Acompanhamento de Status do Pedido), que cobria apenas o detalhe de um pedido específico — esta feature adiciona a listagem que faltava.

## Objetivo
Permitir que o cliente consulte pedidos antigos (não só o que acabou de fazer), sem depender de suporte para reconsultar um pedido passado.

## Papéis envolvidos
- Cliente Final (persona 1, documento 01)

## Fluxo principal
1. Cliente autenticado acessa "Meus Pedidos" (novo item de navegação — ex: dropdown da conta no header do site)
2. Sistema lista todos os pedidos do cliente, mais recentes primeiro, com paginação
3. Cada item da lista mostra: número do pedido, data, status atual, valor total
4. Cliente clica em um pedido para abrir o detalhe (reaproveita a tela do documento 08)

## Telas
- Tela nova: listagem "Meus Pedidos" (não havia print de referência para esta tela nos materiais originais — segue o mesmo padrão visual das tabelas/listas já usadas no site, conforme o design system, documento 14)

## Regras de negócio
- Lista apenas pedidos do `user_id` do cliente autenticado (mesma regra de autorização do documento 08 — reutilizar a Policy já implementada)
- Ordenação padrão: mais recente primeiro (`created_at desc`)
- Paginação: 10 pedidos por página (ajustável, sem necessidade de infinite scroll no MVP)
- Status exibido reflete `orders.status` em tempo real, mesma fonte de dado do Kanban do admin (documento 10) e do detalhe do pedido (documento 08)

## Critérios de aceite
- [ ] Lista exibe todos os pedidos do cliente autenticado, e nenhum pedido de outro cliente
- [ ] Ordenação está correta (mais recente primeiro)
- [ ] Paginação funciona corretamente quando o cliente tem mais de 10 pedidos
- [ ] Clicar em um pedido da lista abre o detalhe correto (documento 08)
- [ ] Cliente sem nenhum pedido vê um estado vazio apropriado, não uma tela quebrada ou erro

## Fora de escopo
- Filtro/busca dentro do histórico (por período, por status)
- Reordenar/repetir um pedido antigo ("pedir de novo")
- Exportar histórico (PDF, CSV)
- Nota fiscal ou comprovante formal
