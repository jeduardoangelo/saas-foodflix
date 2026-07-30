# Feature: Login de Administrador

> Documento 9 de N. Parte do MVP (documento 1).

## Objetivo
Garantir acesso seguro e isolado ao painel administrativo, separado da autenticação de clientes.

## Papéis envolvidos
- Administrador/Operador (persona 2, documento 1)

## Fluxo principal
1. Administrador acessa a URL do painel (`/admin`, documento 3)
2. Preenche email e senha
3. Sistema autentica via guard `admin` (tabela `admins`, documento 2 — isolada de `users`)
4. Administrador é redirecionado ao Dashboard

## Telas
- `admin-login.png` — Painel de Controle (email, senha, "Esqueceu sua senha?")

## Regras de negócio
- Autenticação de admin é completamente isolada da autenticação de cliente (guards e tabelas separados, documento 3) — comprometer uma não compromete a outra
- Não há autocadastro de administrador pela interface pública — contas de admin são criadas via seeder/comando artisan ou por um admin existente (a detalhar na implementação, mas nunca por formulário público)
- Rotas sob `/admin` exigem `auth:admin` em todas as páginas, exceto a própria tela de login

## Critérios de aceite
- [ ] Login com credenciais válidas de admin dá acesso ao Dashboard
- [ ] Login com credenciais de cliente (tabela `users`) é rejeitado no painel admin
- [ ] Tentativa de acessar qualquer rota `/admin/*` sem sessão de admin redireciona para o login
- [ ] Mensagens de erro não revelam se o email existe ou não

## Fora de escopo
- Fluxo de "Esqueceu sua senha?" (visível no print, mesma decisão do documento 4 — fica para iteração seguinte)
- Múltiplos níveis de permissão de admin (ex: admin master vs. operador de cozinha) — no MVP existe um único papel de administrador (documento 1, Personas)
- Autenticação de dois fatores
