# Foodflix — Execução Prática

> Documento 15 de N (último do planejamento inicial). Como estruturar tudo isso em um projeto real, para apontar o Codex a um documento por vez.

## 1. Criação do repositório

- Repositório único (não há necessidade de múltiplos repositórios/monorepo — é uma aplicação Laravel única, site + admin no mesmo codebase, guards diferentes, conforme documento 3)
- Estrutura inicial: `laravel new foodflix` (ou `composer create-project`), seguido da criação da pasta `docs/` na raiz do projeto
- Git Flow desde o início: branch `main` (produção), `develop` (integração), `feature/<nome-da-feature>` por documento de spec (ex: `feature/checkout`, `feature/dashboard-kanban`)

## 2. Onde a documentação vive dentro do projeto

Todos os documentos gerados neste planejamento vão para `docs/` na raiz do repositório — versionados junto com o código, não soltos em outro lugar:

```
foodflix/
└── docs/
    ├── 01-plano-consolidado.md
    ├── 02-modelo-de-dados.md
    ├── 03-arquitetura-tecnica.md
    ├── 13-escopo-mvp-vs-divida-tecnica.md
    ├── 14-design-system.md
    ├── 15-execucao-pratica.md
    └── features/
        ├── 04-feature-cadastro-login-cliente.md
        ├── 05-feature-catalogo-produto.md
        ├── 06-feature-carrinho.md
        ├── 07-feature-checkout.md
        ├── 08-feature-status-pedido-cliente.md
        ├── 09-feature-login-admin.md
        ├── 10-feature-dashboard-kanban-pedidos.md
        ├── 11-feature-gestao-produtos.md
        └── 12-feature-configuracoes-loja.md
```

**Por que isso importa na prática:** ao trabalhar com o Codex, você aponta o prompt para um arquivo específico (ex: "implemente `docs/features/07-feature-checkout.md`, usando `docs/02-modelo-de-dados.md` como referência de schema"), em vez de colar o documento inteiro toda vez ou depender de memória de conversa. Isso também permite atualizar uma spec isoladamente conforme o projeto evolui, sem reescrever tudo.

## 3. Ordem de implementação por dependência

Não é "leia tudo e construa" — é uma sequência em que cada etapa só começa quando a anterior já existe como base. Ordem recomendada:

1. **Fundação de dados** — migrations e models de `categories`, `products`, `store_settings`, `admins`, `users`, `addresses` (documento 2). Nada funciona sem isso existir primeiro.
2. **Login de Administrador** (documento 9) — depende só de `admins`. É a primeira feature "de verdade" a implementar porque destrava o teste manual de tudo que vem depois pelo lado do admin.
3. **Gestão de Produtos** (documento 11) — depende de `categories`/`products` já existirem. Sem produtos cadastrados, o site não tem o que exibir.
4. **Configurações da Loja** (documento 12) — depende só de `store_settings`; pode ser feita em paralelo com o passo 3.
5. **Catálogo e Detalhe de Produto** (documento 5) — depende dos produtos cadastrados no passo 3 já existirem no banco.
6. **Cadastro e Login de Cliente** (documento 4) — depende de `users`/`addresses`; pode ser feita em paralelo com o passo 5.
7. **Carrinho** (documento 6) — depende do catálogo (passo 5) existir para ter o que adicionar.
8. **Checkout + integração de gateway de pagamento** (documento 7) — depende do carrinho (passo 7), do cliente autenticado (passo 6) e das configurações de taxa de entrega (passo 4). É a etapa mais complexa — deixar por último entre as features de site.
9. **Dashboard e Kanban de Pedidos** (documento 10) — depende de pedidos existirem (ou seja, do passo 8 já estar gerando registros em `orders`). É o último passo natural: só faz sentido ver pedidos no Kanban depois que o checkout consegue criá-los.
10. **Acompanhamento de Status do Pedido pelo cliente** (documento 8) — depende de `orders` existir e de o Kanban (passo 9) já atualizar o campo `status`.

**Ponto de validação intermediário:** ao final do passo 8, você já tem o ciclo essencial funcionando ponta a ponta (cliente pede e paga) — mesmo que o admin ainda não tenha visão bonita disso no Kanban. É um bom checkpoint para testar manualmente que os dados estão sendo persistidos corretamente antes de construir a camada de visualização do admin.

## 4. Ambiente de desenvolvimento com Docker

Você mencionou nas notas do seu prompt original que isso é "caso eu peça" — ainda não pedi os arquivos, então não vou gerar `docker-compose.yml`/`Dockerfile`/config de `nginx` agora. Quando quiser, é só avisar e eu monto:

- `Dockerfile` para PHP-FPM com as extensões que o Laravel precisa
- `docker-compose.yml` com serviços de app, banco de dados (MySQL/PostgreSQL — depende da decisão pendente do documento 3) e Nginx
- Configuração de Nginx apontando para `public/` do Laravel

---

Com este documento, o planejamento inicial do Foodflix está completo — os 15 documentos em `docs/` cobrem da visão de produto até a ordem de implementação. Qualquer ajuste de escopo daqui pra frente, é só me chamar para atualizar o documento específico afetado.
