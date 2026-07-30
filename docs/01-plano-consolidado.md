# Foodflix — Plano Consolidado

> Documento 1 de N do planejamento do Foodflix. Este arquivo é a visão geral; os demais documentos (modelo de dados, arquitetura técnica, specs de feature, escopo MVP vs. dívida técnica, design system) detalham partes específicas e devem ser lidos junto com este.

## 1. Visão Geral

O Foodflix é um SaaS de pedidos de comida para uma lanchonete própria (comercializada com a marca "Comidaflix" nas telas de exemplo). O sistema é composto por dois módulos:

- **Site do cliente** — onde o consumidor final navega o cardápio (lanches, pizzas, pastéis, sobremesas, bebidas), monta o carrinho e finaliza o pedido.
- **Painel Administrativo** — onde o dono/operador da lanchonete acompanha pedidos em tempo real, gerencia o catálogo de produtos e configura dados da loja.

**Escopo de negócio:** sistema **single-tenant** — construído para uma única empresa (Foodflix), sem necessidade de suportar múltiplos clientes/lojas na mesma base. Isso simplifica a arquitetura: não há isolamento multi-cliente, provisionamento de novo tenant, nem propagação de atualizações entre instâncias.

**Stack de desenvolvimento:** implementação via Codex no VS Code, seguindo convenções Laravel/PHP já estabelecidas em outros projetos do desenvolvedor.

**Referência visual:** protótipo Figma fornecido, cobrindo login/dashboard/produtos/clientes/configurações do admin e cadastro/login/produto/carrinho/checkout do site.

---

## 2. Personas

### Persona 1 — Cliente Final
Pessoa que acessa o site para pedir comida, majoritariamente pelo celular, com alta sensibilidade a fricção e tempo de decisão curto.

**Jornada:**
1. Descoberta do site (acesso direto ou indicação)
2. Navegação por categorias e destaques ("Os Mais Pedidos")
3. Avaliação do produto (foto, descrição, ingredientes, preço)
4. Montagem do carrinho (ajuste de quantidade, remoção de itens)
5. Cadastro/login
6. Checkout (entrega ou retirada, endereço, pagamento, confirmação)
7. Acompanhamento do status do pedido
8. Recorrência — retorno para novos pedidos

### Persona 2 — Administrador/Operador
Dono da lanchonete ou funcionário responsável por operar o painel administrativo — no escopo atual, um único perfil de usuário administrativo (sem papéis diferenciados de cozinha/entrega).

**Jornada:**
1. Login no painel
2. Visão geral (dashboard com novos pedidos, faturamento do dia, pedidos em andamento/concluídos)
3. Operação do Kanban de pedidos (Novos → Em Preparo → Saiu para Entrega → Concluídos)
4. Gestão de catálogo (criar/editar/ativar/desativar produtos)
5. Configuração da loja (dados, horário de funcionamento, taxa de entrega, tempo estimado)

**Observação de escopo:** não há hoje um terceiro perfil operacional (ex: app de entregador). O avanço de status de entrega é feito manualmente pelo administrador no Kanban.

---

## 3. Princípios de UX por Módulo

| Módulo | Prioridade principal | Por quê |
|---|---|---|
| Site — Catálogo/Produto | Velocidade de conversão | Decisão de compra por impulso/fome; cada fricção a mais reduz conversão |
| Site — Catálogo/Produto | Transparência de preço | Preço visível em todo momento, sem surpresas no checkout |
| Site — Carrinho/Checkout | Baixo compromisso inicial | Permitir montar carrinho sem exigir login; só pedir conta ao finalizar |
| Site — Checkout | Confiança e clareza | Resumo do pedido sempre visível; forma de pagamento e endereço em destaque |
| Admin — Pedidos | Eficiência operacional | Uso sob pressão em horário de pico; Kanban precisa ser escaneável em segundos |
| Admin — Pedidos | Informação certa no momento certo | Dados do cliente e entrega visíveis sem navegação extra |
| Admin — Produtos/Config | Simplicidade administrativa | Uso esporádico, não diário — clareza prioritária sobre velocidade |
| Admin — Produtos/Config | Prevenção de erro | Ações reversíveis e óbvias (ex: toggle ativo/inativo) |

---

## 4. Decisão de Arquitetura

**Não aplicável neste projeto.** O Foodflix é um sistema para uma única empresa, não um produto white-label vendido a múltiplos clientes. Portanto, não há decisão de multi-tenancy a ser tomada — a arquitetura é single-tenant desde o início, sem necessidade de isolar dados por cliente, provisionar novas instâncias, ou propagar atualizações entre deploys separados.

---

## 5. Escopo do MVP

**Abordagem escolhida:** fluxo essencial ponta a ponta — site simplificado + admin simplificado entregues juntos, validando o ciclo completo do pedido antes de aprofundar qualquer módulo isoladamente.

### Entra no MVP

**Site (cliente):**
- Catálogo com categorias (Lanches, Pizzas, Pastéis, Bebidas) e página de produto
- Carrinho (adicionar, alterar quantidade, remover)
- Cadastro e login de cliente
- Checkout completo: entrega ou retirada, endereço, **pagamento online via gateway (Stripe/Pagar.me ou similar)**, resumo, confirmação
- Tela de status/detalhes do pedido

**Admin:**
- Login do administrador
- Dashboard com Kanban de pedidos (Novos → Em Preparo → Saiu para Entrega → Concluídos) e modal de detalhes do pedido
- Cadastro/edição de produtos (nome, categoria, preço, imagem, ativo/inativo)
- Configurações da loja (dados, horário de funcionamento, taxa de entrega, tempo estimado)

### Fica fora do MVP

- Tela de gestão de Clientes no admin (histórico/listagem) — dado existe no banco, tela fica para fase 2
- Avaliações/reviews de produto
- Notificações (sino de admin, push/e-mail ao cliente sobre mudança de status)
- Cupons/motor de promoções (banners promocionais ficam estáticos)
- Papel de "entregador" separado — status de entrega continua manual, pelo admin

---

## 6. Roadmap por Fases

**Fase 1 — MVP (fluxo ponta a ponta):**
Catálogo, carrinho, checkout com pagamento online, cadastro/login de cliente, painel admin com Kanban de pedidos, gestão de produtos e configurações da loja.

**Fase 2 — Consolidação operacional:**
Tela de gestão de Clientes no admin, notificações de status de pedido (cliente e admin), refinamento de relatórios/faturamento no dashboard.

**Fase 3 — Crescimento e engajamento:**
Avaliações de produto, cupons/promoções com motor de desconto, programa de recorrência/fidelidade.

**Fase 4 — Operação avançada (avaliar sob demanda):**
Perfil dedicado de entregador (se o volume justificar um fluxo separado de logística), integração com apps de delivery externos, multi-loja (caso o negócio decida expandir para mais de uma unidade — reabriria a decisão de arquitetura do ponto 4).

---

*Próximo documento: modelo de dados técnico (`.md`), com entidades, campos, tipos e relacionamentos, pensado para consumo por agente de desenvolvimento.*
