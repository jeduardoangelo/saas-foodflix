# Feature: Configurações da Loja (Admin)

> Documento 12 de N. Parte do MVP (documento 1).

## Objetivo
Permitir que o administrador mantenha atualizados os dados operacionais que afetam o site do cliente (endereço, horário, taxa de entrega), sem depender de deploy/alteração de código.

## Papéis envolvidos
- Administrador/Operador (persona 2, documento 1)

## Fluxo principal
1. Administrador acessa "Configurações" no menu do painel
2. Edita: nome da loja, CNPJ, endereço físico, horário de funcionamento, taxa de entrega, tempo estimado de entrega
3. Salva as alterações — valores passam a valer imediatamente no site e no cálculo de novos pedidos

## Telas
- `admin-configuracao.png` — Configurações da Loja (Informações da Empresa, Horário de Funcionamento, Configurações de Entrega)

## Regras de negócio
- Existe uma única linha em `store_settings` (tabela singleton, documento 2) — não há multi-loja no MVP
- Alterar `delivery_fee` ou `estimated_delivery_minutes` afeta apenas pedidos **novos**, feitos após a alteração — pedidos já criados mantêm seus valores (via snapshot em `orders`, documento 2)
- Horário de funcionamento é texto livre no MVP (não bloqueia pedidos fora do horário automaticamente) — validação de "loja fechada" fica fora de escopo por ora

## Critérios de aceite
- [ ] Alterações salvas em Configurações refletem no site (ex: nova taxa de entrega aparece no próximo checkout)
- [ ] Pedidos já concluídos não são afetados por mudanças posteriores na taxa de entrega
- [ ] Campos obrigatórios (nome da loja, CNPJ, endereço) não podem ser salvos vazios

## Fora de escopo
- Bloqueio automático de pedidos fora do horário de funcionamento (ex: loja "fechada" impedindo checkout) — o horário no MVP é apenas informativo, exibido no site
- Múltiplas taxas de entrega por região/distância — taxa única fixa no MVP
- Histórico de alterações de configuração (auditoria)
