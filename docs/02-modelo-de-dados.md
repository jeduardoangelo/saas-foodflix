# Foodflix — Modelo de Dados Técnico

> Documento 2 de N. Escopo: entidades necessárias para o MVP definido no plano consolidado (documento 1). Convenções: `id` = bigint auto-increment (padrão Laravel), timestamps `created_at`/`updated_at` implícitos em todas as tabelas salvo indicação contrária, moedas em `decimal(10,2)`, todos os textos livres em `varchar`/`text` conforme indicado.

## Convenções gerais para o agente de desenvolvimento

- Nomenclatura de tabelas: snake_case, plural (`products`, `orders`, `order_items`).
- Chaves estrangeiras: `<entidade_singular>_id` (ex: `product_id`).
- Enums são implementados como `string` com validação em nível de aplicação (não `enum` nativo do banco), para facilitar evolução sem migration destrutiva.
- Soft delete (`deleted_at`) recomendado em `products` e `users` (histórico de pedidos não pode quebrar se um produto ou cliente for removido).
- Valores monetários sempre em `decimal(10,2)`, nunca `float`.

---

## Entidade: `users` (Cliente Final)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| name | varchar(255) | not null | Nome completo |
| cpf | varchar(14) | not null, unique | Formato mascarado ou só dígitos — definir na spec de feature de cadastro |
| email | varchar(255) | not null, unique | |
| password | varchar(255) | not null | Hash (bcrypt/argon2 via Laravel) |
| phone | varchar(20) | nullable | Não aparece no cadastro do print, mas necessário para contato de entrega — confirmar na spec de feature |
| email_verified_at | timestamp | nullable | |
| deleted_at | timestamp | nullable | Soft delete |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** `users` 1—N `addresses`; `users` 1—N `orders`.

---

## Entidade: `addresses` (Endereço de Entrega)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| user_id | bigint | FK → users.id, not null | |
| street | varchar(255) | not null | "Endereço (Ex: Rua, Avenida)" |
| number | varchar(20) | not null | |
| neighborhood | varchar(100) | not null | "Bairro" |
| city | varchar(100) | not null | |
| complement | varchar(255) | nullable | "Complemento (Opcional)" |
| is_default | boolean | default true | Endereço padrão do cliente |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** N—1 `users`.

**Nota para o agente:** o checkout (ver print `site-finalizar-pedido`) reaproveita o mesmo formato de campos do cadastro — a spec de feature de checkout deve decidir se o endereço é sempre reaproveitado do cadastro ou se pode ser editado por pedido. Recomendação: manter uma cópia imutável do endereço em `orders` (snapshot) para não corromper o histórico caso o cliente edite o endereço depois.

---

## Entidade: `admins` (Administrador/Operador)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| name | varchar(255) | not null | |
| email | varchar(255) | not null, unique | |
| password | varchar(255) | not null | |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** tabela isolada de `users` — autenticação e guard separados (Laravel: dois guards, `web` para cliente e `admin` para painel). Sem papéis diferenciados no MVP (documento 1, seção Personas).

---

## Entidade: `categories` (Categoria de Produto)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| name | varchar(100) | not null, unique | Ex: "Lanches", "Pizzas", "Pastéis", "Bebidas" |
| slug | varchar(100) | not null, unique | Para navegação/URL |
| display_order | integer | default 0 | Ordem de exibição no menu |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** 1—N `products`.

---

## Entidade: `products` (Produto)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| category_id | bigint | FK → categories.id, not null | |
| name | varchar(255) | not null | |
| description | text | nullable | |
| ingredients | text | nullable | Lista de ingredientes (print mostra como texto corrido) |
| price | decimal(10,2) | not null | |
| image_url | varchar(500) | nullable | |
| active | boolean | default true | Toggle "Ativo/Inativo" no admin |
| deleted_at | timestamp | nullable | Soft delete |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** N—1 `categories`; 1—N `order_items`.

**Fora do MVP:** campos de avaliação (`rating_avg`, `rating_count` vistos no print de detalhe do produto) — não modelar agora, ficam para a Fase 3 do roadmap (documento 1).

---

## Entidade: `store_settings` (Configurações da Loja)

Tabela singleton — no MVP, uma única linha (não há multi-loja).

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | Sempre id=1 na prática |
| store_name | varchar(255) | not null | |
| cnpj | varchar(18) | not null | |
| physical_address | varchar(500) | not null | Endereço físico da lanchonete |
| opening_hours | text | not null | Texto livre no print (ex: "Segunda a Sexta: 11h às 23h") — considerar estruturar como JSON por dia da semana em versão futura |
| delivery_fee | decimal(10,2) | not null | |
| estimated_delivery_minutes | integer | not null | |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** nenhuma FK — consumida por toda a aplicação como configuração global.

---

## Entidade: `orders` (Pedido)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| order_number | varchar(20) | not null, unique | Número amigável exibido ao usuário (ex: "#1260" no print) |
| user_id | bigint | FK → users.id, not null | |
| status | varchar(30) | not null, default 'novo' | Valores: `novo`, `em_preparo`, `saiu_para_entrega`, `concluido`, `cancelado` |
| delivery_method | varchar(20) | not null | Valores: `entrega`, `retirada` |
| delivery_address_snapshot | json | nullable | Cópia imutável do endereço no momento do pedido (null se `retirada`) |
| payment_method | varchar(30) | not null | Ex: `cartao_credito`, `pix`, `dinheiro` — detalhar na spec de checkout |
| payment_status | varchar(20) | not null, default 'pendente' | Valores: `pendente`, `pago`, `falhou`, `reembolsado` |
| subtotal | decimal(10,2) | not null | |
| delivery_fee | decimal(10,2) | not null, default 0 | |
| total | decimal(10,2) | not null | |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** N—1 `users`; 1—N `order_items`; 1—1 `payments`.

**Nota para o agente:** o campo `status` alimenta diretamente as colunas do Kanban no admin (documento 1 — Kanban: Novos, Em Preparo, Saiu para Entrega, Concluídos). A transição de status é sempre feita manualmente pelo admin no MVP (sem automação).

---

## Entidade: `order_items` (Item do Pedido)

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| order_id | bigint | FK → orders.id, not null | |
| product_id | bigint | FK → products.id, not null | |
| product_name_snapshot | varchar(255) | not null | Nome do produto no momento da compra (imutável mesmo se o produto for editado depois) |
| unit_price_snapshot | decimal(10,2) | not null | Preço unitário no momento da compra |
| quantity | integer | not null | |
| subtotal | decimal(10,2) | not null | `unit_price_snapshot * quantity` |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** N—1 `orders`; N—1 `products`.

---

## Entidade: `payments` (Pagamento)

Necessária porque o MVP inclui gateway de pagamento online (decisão registrada no documento 1).

| Campo | Tipo | Constraints | Notas |
|---|---|---|---|
| id | bigint | PK, auto-increment | |
| order_id | bigint | FK → orders.id, not null, unique | Relação 1—1 com pedido |
| gateway | varchar(50) | not null | Ex: `stripe`, `pagarme` — definir na spec de feature de checkout |
| gateway_transaction_id | varchar(255) | nullable | ID retornado pelo gateway |
| method | varchar(30) | not null | Ex: `credit_card`, `pix`, `boleto` |
| status | varchar(20) | not null, default 'pendente' | Valores: `pendente`, `aprovado`, `recusado`, `estornado` |
| amount | decimal(10,2) | not null | |
| paid_at | timestamp | nullable | |
| created_at / updated_at | timestamp | not null | |

**Relacionamentos:** 1—1 `orders`.

**Nota para o agente:** dados sensíveis de cartão **nunca** são persistidos nesta tabela — apenas o `gateway_transaction_id` de referência. Tokenização é responsabilidade do gateway (frontend usa SDK do gateway, backend só recebe token/confirmação).

---

## Entidades explicitamente fora do MVP (não modelar agora)

- `product_reviews` (avaliações/estrelas) — Fase 3 do roadmap
- `coupons` / `promotions` — Fase 3 do roadmap
- `notifications` — Fase 2 do roadmap
- `couriers` (entregadores) — Fase 4, condicional
- Qualquer estrutura de multi-loja/multi-tenant — não aplicável a este projeto (documento 1, seção 4)

---

## Diagrama de relacionamento (resumo textual)

```
users 1───N addresses
users 1───N orders
categories 1───N products
products 1───N order_items
orders 1───N order_items
orders 1───1 payments
admins (isolado — sem FK para outras entidades de domínio)
store_settings (isolado — configuração global)
```

---

*Próximo documento: arquitetura técnica (`.md`), com stack, estrutura de pastas e convenções de código.*
