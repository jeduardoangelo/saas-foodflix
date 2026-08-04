# Foodflix — Design System

> Documento 14 de N. Extraído dos prints de referência enviados para a marca "Foodflix". Como o Foodflix é single-tenant (documento 1), este design system **não precisa ser white label/tokens trocáveis por cliente** — é a identidade visual definitiva do produto, não uma base multi-marca.

## 1. Paleta de Cores

| Token | Valor aproximado | Uso |
|---|---|---|
| `color-primary` | Laranja (`#F04E23` aprox.) | Logo, preços em destaque, links, texto de ação secundário |
| `color-primary-cta` | Vermelho (`#E8271A` aprox.) | Botões de ação principal: "Entrar", "Cadastrar", "Adicionar ao Carrinho", "Pedir", "Ir para o Pagamento" |
| `color-dark` | Quase preto (`#1A1A1A` aprox.) | Barra superior do admin (Figma toolbar — não faz parte do produto), textos de título |
| `color-text-primary` | Cinza escuro (`#1F2937` aprox.) | Texto principal, nomes, valores |
| `color-text-secondary` | Cinza médio (`#6B7280` aprox.) | Labels, texto de apoio, placeholders |
| `color-background` | Cinza muito claro (`#F5F6F8` aprox.) | Fundo geral das páginas (site e admin) |
| `color-surface` | Branco (`#FFFFFF`) | Cards, modais, inputs (fundo dos campos é um cinza levemente mais escuro que o card) |
| `color-input-bg` | Cinza claro (`#F0F1F3` aprox.) | Fundo de campos de formulário |
| `color-success` | Verde (`#16A34A` aprox.) | Status "Ativo" na tabela de produtos |
| `color-badge-orange-bg` / `color-badge-orange-text` | Pêssego claro / laranja | Badge de contagem "Total de Pedidos" na tabela de clientes |
| `color-status-new` | Laranja | Coluna/badge "Novos Pedidos" |
| `color-status-preparing` | Amarelo/laranja | Coluna/badge "Em Preparo" |
| `color-status-shipping` | Azul | Coluna/badge "Saiu para Entrega" |
| `color-status-done` | Verde | Coluna/badge "Concluídos" |

## 2. Tipografia

- **Família:** sans-serif geométrica (estilo Inter/Helvetica) — usar `Inter` como padrão de implementação
- **Hierarquia:**
  - Título de página (ex: "Gerenciamento de Produtos"): bold, ~24px
  - Subtítulo/descrição de página (ex: "Gerencie o catálogo de produtos"): regular, cinza secundário, ~14px
  - Nome de produto/cliente em listas: semibold, ~16px
  - Preço em destaque: bold, cor primária, ~16-18px
  - Labels de formulário: semibold, ~13-14px
  - Texto de input/placeholder: regular, ~14px

## 3. Componentes identificados

### Botões
- **Primário (CTA):** fundo vermelho sólido, texto branco, cantos arredondados (~8px), largura frequentemente 100% do container em formulários (ex: "Entrar", "Cadastrar")
- **Secundário (outline):** borda laranja/vermelha, fundo transparente/branco, texto na cor da borda (ex: "Ver Detalhes", "Continuar Comprando")
- **Botão de ícone:** ícones de ação em tabelas (editar = lápis azul, excluir = lixeira vermelha)
- **Toggle switch:** usado para status Ativo/Inativo de produto — preto quando ativo

### Cards
- Fundo branco, cantos arredondados (~12px), sombra suave
- Usado em: cards de resumo do dashboard, cards de pedido no Kanban, cards de produto no catálogo, card de formulário (login, cadastro, checkout)

### Formulários
- Label acima do campo, sempre visível (não usa placeholder como único indicador)
- Campo com fundo cinza claro, sem borda visível forte, cantos arredondados (~8px)
- Campos agrupados em seções com título de seção e linha divisória (ex: "Dados Pessoais", "Endereço de Entrega", "Segurança" no cadastro)

### Navegação
- **Site:** header horizontal fixo — logo à esquerda, menu de categorias ao centro, busca, ícone de carrinho (com badge de contador) e ícone de conta à direita
- **Admin:** sidebar vertical fixa à esquerda — logo/nome do painel no topo, itens de menu com ícone, item ativo destacado com fundo laranja claro e texto laranja; "Sair" fixado embaixo

### Tabelas (admin)
- Header de coluna em texto simples, sem fundo destacado
- Linhas com bastante espaço em branco vertical, sem zebra striping
- Badges numéricos arredondados para contagens (ex: total de pedidos por cliente)

### Kanban (pedidos)
- Colunas com cabeçalho: nome da coluna + badge numérico colorido de contagem
- Cards de pedido dentro de cada coluna: número do pedido, horário, nome do cliente, valor, itens resumidos, botão "Ver Detalhes"

### Modal
- Overlay escuro semitransparente sobre o conteúdo
- Modal branco centralizado, cantos arredondados, título + botão de fechar (X) no canto superior direito
- Seções internas com subtítulos (ex: "Dados do Cliente", "Resumo do Pedido", "Itens do Pedido", "Valores")

## 4. Padrões de espaçamento

- Espaçamento generoso entre seções de card (~24-32px)
- Padding interno de card/formulário consistente (~24px)
- Grid de produtos no catálogo: colunas com gap consistente, cards de largura igual
- Alinhamento consistente de valores monetários à direita em tabelas e resumos

## 5. Observação sobre reutilização

Como definido no início deste documento, não há necessidade de arquitetar os tokens de cor/tipografia como "trocáveis por cliente" — isso só faria sentido em um produto white-label (como o projeto Trindade). Aqui, os tokens acima podem ser implementados diretamente como constantes de tema (ex: variáveis CSS ou config do Tailwind), sem camada de indireção para múltiplas marcas.

---

*Próximo passo: execução prática (ponto 11) — como estruturar isso em um projeto real.*
