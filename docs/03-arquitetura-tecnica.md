# Foodflix — Arquitetura Técnica

> Documento 3 de N. Este documento não cobre propagação de atualizações entre instâncias nem provisionamento de novo cliente — não se aplicam, pois o Foodflix é single-tenant (documento 1, seção 4).

## 1. Stack

- **Backend:** Laravel (PHP), seguindo convenções já utilizadas em outros projetos: uso de `config('services.x.y')` para configuração de serviços externos, autenticação via `tymon/jwt-auth`.
- **Frontend do site do cliente e do admin:** a definir pela equipe no momento da implementação — pode ser Blade + Livewire/Alpine.js (mantém tudo no ecossistema Laravel) ou uma SPA separada consumindo API. **Recomendação para MVP:** Blade + Livewire, por reduzir a superfície de coordenação entre repositórios/deploys para um projeto novo e pequeno como este.
- **Banco de dados:** MySQL ou PostgreSQL (a definir — qualquer um atende o volume esperado de um single-tenant).
- **Gateway de pagamento:** integração via SDK do gateway escolhido (Stripe ou Pagar.me — decisão registrada no documento 1). Chaves de API nunca em arquivos de configuração versionados — sempre via variáveis de ambiente (`.env`), seguindo a prática já adotada de migrar tokens para fora de arquivos JSON de config.
- **Autenticação:** dois guards Laravel separados — `web` (clientes) e `admin` (painel administrativo) — refletindo a separação das tabelas `users` e `admins` do modelo de dados (documento 2).
- **Versionamento:** Git Flow (branches `main`, `develop`, `feature/*`, `hotfix/*`).

## 2. Estrutura de pastas (alto nível)

```
foodflix/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Site/          # Controllers do site do cliente
│   │   │   └── Admin/         # Controllers do painel admin
│   │   ├── Middleware/
│   │   └── Requests/          # Form Requests para validação
│   ├── Models/
│   │   ├── User.php
│   │   ├── Admin.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Payment.php
│   │   ├── Address.php
│   │   └── StoreSetting.php
│   ├── Services/
│   │   ├── Payment/           # Integração com gateway de pagamento
│   │   ├── Order/             # Regras de negócio de criação/transição de status de pedido
│   │   └── Cart/              # Regras de cálculo de carrinho (subtotal, taxa de entrega)
│   └── Policies/               # Autorização (admin vs. cliente)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── site/
│       └── admin/
├── routes/
│   ├── web.php                # Rotas do site
│   ├── admin.php               # Rotas do painel admin (prefix /admin)
│   └── api.php                 # Se houver consumo por SPA/mobile futuro
├── docs/                        # Documentação viva do projeto (ver seção "Execução prática")
│   ├── 01-plano-consolidado.md
│   ├── 02-modelo-de-dados.md
│   ├── 03-arquitetura-tecnica.md
│   ├── 04-escopo-mvp-vs-divida-tecnica.md
│   ├── features/
│   └── design-system.md
└── tests/
```

## 3. Convenções de código

- **Controllers finos, Services gordos:** regras de negócio (cálculo de total do pedido, transição de status, integração de pagamento) vivem em `app/Services/`, não nos Controllers.
- **Form Requests** para toda validação de entrada (cadastro, checkout, criação de produto) — não validar direto no Controller.
- **Snapshots de dados históricos:** como definido no modelo de dados, `order_items` e `orders` guardam cópias imutáveis (`product_name_snapshot`, `unit_price_snapshot`, `delivery_address_snapshot`) — nunca fazer `join` para buscar preço/endereço atual ao exibir um pedido já concluído.
- **Configuração de serviços externos:** sempre via `config('services.<nome>.<chave>')`, populado a partir de `.env` — nunca hardcoded, nunca em JSON versionado.
- **Guards separados:** rotas de admin sempre sob middleware `auth:admin` e prefixo `/admin`; rotas de cliente sob `auth:web` quando aplicável.
- **Drivers de autenticação:** os guards `web` e `admin` usam sessão e providers Eloquent separados. O pacote `tymon/jwt-auth` permanece sem uso no fluxo web; futuramente será utilizado somente por um guard `api` dedicado à integração com aplicativo mobile.
- **Configuração singleton da loja:** todo acesso a `store_settings` deve passar por `StoreSetting::current()`, que retorna exclusivamente a linha de `id=1` e nunca cria dados automaticamente. Se a linha não existir, execute `php artisan db:seed --class=StoreSettingSeeder`. Features futuras não devem chamar `StoreSetting::create()` diretamente. Os valores de CNPJ, endereço e horário criados pelo seeder são placeholders e precisam ser substituídos pela tela de Configurações antes da produção.
- **Nomenclatura:** tabelas em snake_case plural, Models em PascalCase singular (padrão Laravel).

## 4. O que este documento **não** cobre (não aplicável)

- Estratégia de propagação de atualizações entre instâncias — não há múltiplas instâncias (single-tenant, documento 1).
- Processo de provisionamento de novo cliente — não há onboarding de novos clientes/tenants neste produto.

---

*Próximo passo: você confirma o escopo de features do MVP (ponto 8) para eu gerar as specs, uma por documento.*
