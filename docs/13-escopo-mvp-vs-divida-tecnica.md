# Foodflix — Escopo MVP vs. Dívida Técnica

> Documento 13 de N. Separa três categorias: (A) features do MVP — já detalhadas nos documentos 4–12; (B) mínimo obrigatório mesmo não sendo feature visível; (C) dívida técnica conscientemente adiada, com critério de quando deixa de ser aceitável adiar.

## A. Necessário implementar agora (features do MVP)

Já coberto integralmente pelos documentos 4 a 12 (Cadastro/Login Cliente, Catálogo/Produto, Carrinho, Checkout, Status do Pedido, Login Admin, Dashboard/Kanban, Gestão de Produtos, Configurações da Loja). Não repetido aqui — ver cada documento para critérios de aceite.

## B. Mínimo obrigatório mesmo não sendo feature

Itens que não aparecem em nenhum print, mas que são pré-requisito para operar em produção com segurança e de forma legal:

| Item | Por que é obrigatório | Onde se aplica |
|---|---|---|
| Hash de senha (bcrypt/argon2) | Padrão mínimo de segurança — nunca senha em texto puro | `users`, `admins` (documento 2) |
| HTTPS obrigatório em produção | Dados de pagamento e pessoais (CPF, endereço) trafegando na rede | Toda a aplicação |
| Variáveis de ambiente para credenciais de gateway | Chave de API do gateway de pagamento nunca em código versionado | `Services/Payment` (documento 3) |
| Rate limiting em login (site e admin) | Prevenção básica de força bruta | Rotas de autenticação |
| Validação de CPF (formato/dígito verificador) | Dado sensível coletado no cadastro — mínimo de qualidade de dado | Feature de Cadastro (documento 4) |
| Backup automático do banco de dados | Perda de dados de pedidos/pagamentos é inaceitável mesmo em MVP | Infraestrutura |
| Log de erros de pagamento | Sem isso, uma falha de cobrança vira um mistério não rastreável | `Services/Payment` |
| Conformidade LGPD básica (aviso de privacidade, finalidade do CPF/endereço coletado) | Dado pessoal sendo coletado (CPF, endereço, telefone) — exige base legal e transparência mínima | Cadastro (documento 4) e Checkout (documento 7) |

Nenhum desses itens tem tela própria nos prints, mas todos são **bloqueadores de ir para produção**, não "nice to have".

## C. Dívida técnica conscientemente adiada

| Item adiado | Justificativa para adiar agora | Critério de quando deixa de ser aceitável |
|---|---|---|
| Carrinho client-side (sem persistência entre dispositivos) | Reduz complexidade inicial; maioria dos pedidos é feita em uma sessão única | Se dados de suporte mostrarem clientes reclamando de carrinho perdido ao trocar de aparelho/navegador |
| Ausência de notificações (push/e-mail de status) | Cliente pode acompanhar manualmente na tela de status | Assim que o volume de pedidos/dia crescer a ponto de gerar contato de suporte perguntando "meu pedido chegou em que status?" |
| Sem tela "Meus Pedidos" (histórico) | Prints fornecidos não mostravam essa tela; cliente ainda consegue ver o pedido recém-feito | No momento em que clientes recorrentes precisarem consultar pedidos antigos (ex: reclamação, nota fiscal) |
| Horário de funcionamento apenas informativo (não bloqueia pedido fora do horário) | Simplifica o checkout no MVP | Se a loja receber pedidos fora do horário que não consegue atender, virando problema operacional recorrente |
| Sem múltiplos endereços por cliente | Modelo de dados já suporta (`addresses` é 1—N), só falta a tela | Quando um número relevante de clientes pedir para casa e trabalho recorrentemente com endereços diferentes |
| Sem cancelamento de pedido pelo cliente | Não estava nos prints; cancelamento é só via admin por ora | Se o volume de pedidos errados/cancelados gerar carga operacional alta demais para o admin lidar manualmente |
| Sem múltiplos papéis de admin (dono vs. operador de cozinha) | Um único perfil administrativo é suficiente para uma operação pequena | Quando a lanchonete tiver equipe grande o suficiente para precisar restringir o que cada funcionário pode ver/editar |
| Sem auditoria de alterações em Configurações da Loja | Baixo risco para uma única loja controlada pelo próprio dono | Se mais de uma pessoa tiver acesso de admin e for necessário rastrear "quem mudou a taxa de entrega" |
| Agendamento automático do backup do banco | O comando manual `php artisan backup:database` está disponível, mas o mecanismo de agendamento depende da infraestrutura de produção | Assim que a hospedagem for escolhida, configurar e monitorar cron/scheduler diário antes de colocar a aplicação em produção |

O backup manual é apenas a preparação operacional. O agendamento e o monitoramento de sua execução continuam explicitamente pendentes e são bloqueadores para produção até a definição da hospedagem.

## Observação geral

A linha entre B e C é intencional: **B nunca é dívida técnica** — são pré-requisitos de operar com responsabilidade, não itens que "dá pra deixar para depois". C é dívida técnica de verdade: escolhas conscientes de escopo reduzido, com um gatilho claro de quando reavaliar.

---

*Próximo documento: design system (`.md`), extraído dos prints de referência enviados.*
