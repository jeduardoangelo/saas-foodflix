# Feature: Cadastro e Login de Cliente

> Documento 4 de N. Parte do MVP (documento 1).

## Objetivo
Permitir que o cliente final crie uma conta e acesse o site autenticado, viabilizando checkout com dados reaproveitados (endereço) e histórico de pedidos.

## Papéis envolvidos
- Cliente Final (persona 1, documento 1)

## Fluxo principal

**Cadastro:**
1. Cliente acessa "Cadastre-se" a partir da tela de login
2. Preenche: nome completo, CPF, email, telefone, endereço de entrega (rua, número, bairro, cidade, complemento opcional), senha e confirmação de senha
3. Sistema valida os campos e cria o registro em `users` + `addresses` (documento 2)
4. Cliente é autenticado automaticamente e redirecionado ao site

**Login:**
1. Cliente acessa "Acesse sua Conta"
2. Preenche email e senha
3. Sistema autentica via guard `web` (documento 3)
4. Cliente é redirecionado à página anterior ou à home

## Telas
- `site-cadastro-usuario.png` — Crie sua Conta (Dados Pessoais, Endereço de Entrega, Segurança)
- `site-login-usuario.png` — Acesse sua Conta

## Regras de negócio
- Email e CPF são únicos por cliente (constraint em `users`, documento 2)
- Senha exige confirmação idêntica no cadastro
- Endereço informado no cadastro é salvo como endereço padrão (`is_default = true` em `addresses`)
- Login incorreto exibe mensagem genérica (não revelar se o erro é de email ou senha, por segurança)
- Telefone é obrigatório (necessário para contato durante a entrega, conforme observação registrada no planejamento inicial) — formato brasileiro com DDD

## Critérios de aceite
- [ ] Cliente consegue se cadastrar com dados válidos e é autenticado automaticamente ao final
- [ ] Sistema rejeita cadastro com email ou CPF já existentes, com mensagem clara
- [ ] Cliente consegue fazer login com credenciais válidas
- [ ] Tentativa de login com credenciais inválidas é rejeitada sem revelar qual campo está incorreto
- [ ] Senha é armazenada com hash (nunca em texto puro)

## Fora de escopo
- Login social (Google/Facebook)
- Recuperação de senha — passou a ser coberta pelo documento 17-feature-recuperacao-senha.md
- Múltiplos endereços por cliente (cadastro/gestão de mais de um endereço) — modelo de dados já suporta (`addresses` é 1—N), mas a tela de gestão de múltiplos endereços não é MVP
- Verificação de email (`email_verified_at` existe no modelo para uso futuro, não é obrigatória no MVP)
