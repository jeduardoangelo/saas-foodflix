# Feature: Catálogo e Detalhe de Produto

> Documento 5 de N. Parte do MVP (documento 1).

## Objetivo
Permitir que o cliente navegue pelo cardápio, entenda cada produto e decida o que comprar com o mínimo de fricção (princípio de UX: velocidade de conversão e transparência de preço, documento 1).

## Papéis envolvidos
- Cliente Final (persona 1, documento 1)

## Fluxo principal
1. Cliente acessa a home e vê banner promocional, seção "Os Mais Pedidos" e categorias (Lanches, Pizzas, Pastéis, Bebidas)
2. Cliente navega por categoria pelo menu superior ou usa a busca ("Buscar por lanche, pizza...")
3. Cliente clica em "Ver Detalhes" de um produto
4. Página de detalhe exibe imagem, nome, categoria, descrição, ingredientes e preço
5. Cliente ajusta quantidade e adiciona ao carrinho, ou clica "Pedir" direto do card (adiciona 1 unidade sem passar pelo detalhe)

## Telas
- `site.png` — Home com banner, "Os Mais Pedidos" e categorias
- `site-produto.png` — Detalhe do produto (imagem, descrição, ingredientes, preço, seletor de quantidade)

## Regras de negócio
- Apenas produtos com `active = true` (documento 2) aparecem no catálogo do cliente
- Preço exibido é sempre o `price` atual do produto — não há histórico de preço nesta tela (o histórico fica registrado só dentro dos pedidos já feitos, via snapshot em `order_items`)
- Botão "Pedir" no card adiciona 1 unidade diretamente ao carrinho sem navegar até o detalhe
- Busca filtra por nome do produto (case-insensitive)

## Critérios de aceite
- [ ] Home lista produtos ativos agrupados por categoria
- [ ] Busca retorna produtos cujo nome contém o termo buscado
- [ ] Página de detalhe exibe todos os campos do produto corretamente
- [ ] Ajuste de quantidade no detalhe recalcula o total exibido antes de adicionar ao carrinho
- [ ] Produtos inativos não aparecem em nenhuma listagem do site

## Fora de escopo
- Sistema de avaliações/estrelas (visível no print como "4.5 (82 avaliações)") — Fase 3 do roadmap (documento 1)
- Filtros avançados (preço, restrições alimentares)
- Recomendações personalizadas ("Os Mais Pedidos" no MVP é uma lista fixa/manual, não um algoritmo de popularidade real-time)
