# Feature: Recuperação de Senha

> Documento 17 de N. Cobre cliente (guard `web`) e administrador (guard `admin`) — os dois prints de login mostram o link "Esqueceu sua senha?", e as duas tabelas de usuário existem isoladas (documento 02), então o fluxo precisa ser duplicado por guard, não compartilhado.

## Objetivo
Permitir que cliente ou administrador recupere acesso à conta sem depender de suporte manual, mantendo o mesmo padrão de segurança já usado no restante da autenticação (documentos 04 e 09).

## Papéis envolvidos
- Cliente Final (persona 1, documento 01)
- Administrador/Operador (persona 2, documento 01)

## Fluxo principal
1. Usuário clica "Esqueceu sua senha?" na tela de login (site ou admin)
2. Informa o email cadastrado
3. Sistema envia um link de redefinição por email, com token de expiração curta
4. Usuário clica no link, é levado a um formulário de nova senha + confirmação
5. Ao confirmar, a senha é atualizada (com hash) e o token é invalidado
6. Usuário é redirecionado ao login com mensagem de sucesso

## Telas
- Extensão de `site-login-usuario.png` e `admin-login.png` (link já presente nos prints)
- Tela nova: formulário "Esqueci minha senha" (informar email)
- Tela nova: formulário "Redefinir senha" (nova senha + confirmação)

## Regras de negócio
- Mensagem de confirmação de envio de email é **sempre a mesma**, exista ou não o email na base — não revelar se um email está cadastrado (mesmo princípio de segurança do login, documentos 04/09)
- Token de redefinição expira em 60 minutos (padrão do Laravel Password Broker)
- Token é de uso único — invalidado após a redefinição bem-sucedida
- Fluxo é duplicado por guard: um broker de senha para `users`, outro para `admins` — um token de cliente não pode redefinir senha de admin e vice-versa
- Nova senha segue as mesmas regras de confirmação do cadastro (documento 04)

## Critérios de aceite
- [ ] Solicitar redefinição com email existente envia o link corretamente
- [ ] Solicitar redefinição com email inexistente exibe a mesma mensagem de sucesso, sem revelar a ausência do email
- [ ] Link expirado (após 60 min) exibe erro claro e não permite redefinição
- [ ] Após redefinir, login com a senha antiga falha e com a nova funciona
- [ ] Token usado uma vez não pode ser reutilizado
- [ ] Fluxo de cliente não afeta contas de admin e vice-versa (guards isolados)

## Fora de escopo
- Recuperação por SMS/telefone — apenas email
- Autenticação de dois fatores como parte da redefinição
- Notificação de segurança ("sua senha foi alterada") por outros canais além do próprio fluxo
