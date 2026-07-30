# Feature: Carrinho de Compras

> Documento 6 de N. Parte do MVP (documento 1).

## Objetivo
Permitir que o cliente monte, revise e ajuste seu pedido antes de finalizar, sem exigir login (princípio de UX: baixo compromisso inicial, documento 1).

## Papéis envolvidos
- Cliente Final (persona 1, documento 1)

## Fluxo principal
1. Cliente adiciona produtos via botão "Pedir" (card) ou "Adicionar ao Carrinho" (detalhe do produto)
2. Ícone de carrinho no header atualiza contador de itens e exibe mini-carrinho em dropdown
3. Cliente pode alterar quantidade (+/-) ou remover item direto no mini-carrinho ou na página completa "Meu Carrinho"
4. Página completa exibe cada item com imagem, nome, preço unitário, subtotal, e resumo (subtotal geral, taxa de entrega "a calcular", total)
5. Cliente clica "Ir para o Pagamento" para seguir ao checkout, ou "Continuar Comprando" para voltar ao catálogo

## Telas
- `site-atualizar-carrinho.png` — Meu Carrinho (lista de itens + resumo do pedido)
- `site.png` (estado com mini-carrinho aberto) — dropdown de carrinho no header

## Regras de negócio
- Carrinho não exige login — cliente não autenticado pode montar carrinho livremente
- Login só é exigido ao avançar para o checkout (transição carrinho → checkout)
- Taxa de entrega não é calculada no carrinho ("a calcular") — só é definida no checkout, quando o método de entrega/endereço é confirmado (valor vem de `store_settings.delivery_fee`, documento 2)
- Quantidade mínima por item é 1; reduzir para 0 remove o item do carrinho
- Persistência do carrinho: **decisão de implementação a confirmar com o time** — pode ser client-side (localStorage/sessão) para cliente não logado e persistido em banco quando logado. Este documento não define modelo de dados de carrinho porque, no MVP, o carrinho não precisa sobreviver entre dispositivos (ver "Fora de escopo")

## Critérios de aceite
- [ ] Adicionar produto ao carrinho atualiza contador no header imediatamente
- [ ] Alterar quantidade recalcula subtotal do item e total do carrinho em tempo real
- [ ] Remover item retira da lista e recalcula o total
- [ ] Carrinho vazio exibe estado apropriado (não uma tela quebrada)
- [ ] Carrinho é preservado ao navegar entre páginas do site na mesma sessão

## Fora de escopo
- Persistência de carrinho entre dispositivos/sessões distintas (carrinho "na nuvem" vinculado à conta) — MVP considera carrinho válido apenas durante a sessão ativa
- Cupom de desconto aplicado no carrinho (Fase 3 do roadmap, documento 1)
- Sugestão de produtos complementares ("compre também")
