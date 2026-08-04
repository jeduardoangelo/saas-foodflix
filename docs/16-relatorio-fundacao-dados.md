# Foodflix — Relatório da Fundação de Dados

> Registro dos problemas encontrados, correções realizadas e pendências após a implementação do passo 1 de `docs/15-execucao-pratica.md`.

## 1. O que deu errado

### 1.1. O PHP local não é compatível com as dependências instaladas

O ambiente local possui PHP 7.4.33, mas o conjunto atual de dependências do Composer exige PHP 8.3 ou superior. Por isso, comandos como:

```bash
php artisan migrate
```

não podem ser executados diretamente na máquina.

**Solução utilizada:** execução dos comandos dentro do container `app`, que usa PHP 8.3.

**Impacto:** qualquer comando Artisan executado fora do Docker continuará falhando enquanto o PHP local ou as dependências não forem alinhados.

---

### 1.2. O container MySQL não iniciava com as variáveis atuais

O arquivo `.env` define:

```dotenv
DB_USERNAME=root
DB_PASSWORD=
```

O `docker-compose.yml` reutiliza `DB_USERNAME` como `MYSQL_USER`. A imagem oficial do MySQL não permite configurar `MYSQL_USER=root`; o usuário root deve ser configurado somente por `MYSQL_ROOT_PASSWORD`.

O container entrava em ciclo de reinicialização com a mensagem:

```text
MYSQL_USER="root", MYSQL_USER and MYSQL_PASSWORD are for configuring a regular user and cannot be used for the root user
```

**Solução utilizada:** os containers `db` e `app` foram recriados temporariamente com o usuário não-root `foodflix`, sem apagar o volume.

**Impacto:** o problema pode reaparecer em uma futura recriação dos containers se as variáveis do ambiente não forem corrigidas.

**Correção ainda necessária:** separar as credenciais internas do container das credenciais root ou alterar o `.env` para usar um usuário de aplicação não-root.

---

### 1.3. O atalho de timestamps do Laravel não atendia literalmente à spec

No Laravel 8, o comando:

```php
$table->timestamps();
```

cria `created_at` e `updated_at` como nullable. O modelo de dados determina que esses campos sejam `not null`.

**Solução aplicada:** as seis migrations passaram a declarar explicitamente:

```php
$table->timestamp('created_at');
$table->timestamp('updated_at');
```

O schema final foi validado no MySQL e ambos os campos ficaram como `NOT NULL`.

Como a primeira execução no banco principal ocorreu antes desse ajuste, as colunas já criadas foram alinhadas com `ALTER TABLE`, sem apagar dados. Uma nova instalação já recebe diretamente o schema correto pelas migrations.

---

### 1.4. O banco principal não estava disponível na primeira tentativa

A primeira execução de:

```bash
docker compose exec -T app php artisan migrate --force
```

falhou porque o serviço `db` não estava operacional. A mensagem inicial indicava falha na resolução do hostname `db`, causada pelo container MySQL em reinicialização contínua.

**Solução aplicada:** diagnóstico pelos logs, correção temporária das credenciais dos containers e nova execução das migrations.

---

## 2. Pendências encerradas

### 2.1. Singleton de `store_settings`

**Resolvido:** `StoreSettingSeeder` cria somente a linha de `id=1` por
`firstOrCreate`, com os valores iniciais definidos para o projeto. CNPJ,
endereço e horário são placeholders explicitamente documentados e devem ser
substituídos pela tela de Configurações antes da produção.

`StoreSetting::current()` nunca cria registros. O método retorna a linha de
`id=1` ou lança uma exceção orientando a executar:

```bash
php artisan db:seed --class=StoreSettingSeeder
```

A convenção de nunca chamar `StoreSetting::create()` diretamente está
registrada em `docs/03-arquitetura-tecnica.md`. Não foi adicionada constraint
de singleton no banco, conforme a decisão do projeto.

---

### 2.2. Configuração definitiva do driver de autenticação

Os guards estão separados:

- `web` usa o provider `users`;
- `admin` usa o provider `admins`.

**Resolvido:** ambos usam o driver `session`, com providers Eloquent separados.
O pacote `tymon/jwt-auth` permanece instalado, mas será usado futuramente
somente por um guard `api` dedicado ao aplicativo mobile.

---

### 2.3. Criação segura do primeiro administrador

**Resolvido para a fundação:** `AdminSeeder` exige `ADMIN_SEED_EMAIL` e
`ADMIN_SEED_PASSWORD`, rejeita valores ausentes, vazios ou placeholders, e usa
`Hash::make()` antes de persistir a senha. O comportamento está coberto por
testes automatizados.

Os fluxos futuros de cadastro e alteração de senha continuam obrigados a
aplicar hash em suas respectivas Services; eles permanecem fora desta etapa.

---

### 2.4. Testes específicos de schema e models

**Resolvido:** a suíte agora cobre:

- nomes e tipos das colunas;
- constraints únicas;
- chaves estrangeiras;
- casts dos models;
- relacionamentos Eloquent;
- soft deletes de `users` e `products`;
- separação entre os providers `users` e `admins`;
- comportamento singleton de `store_settings`;
- falha segura e hash do `AdminSeeder`.

---

### 2.5. Relacionamentos que apontam para entidades futuras

Os models possuem os relacionamentos previstos na documentação:

- `User::orders()`;
- `Product::orderItems()`.

Os models `Order` e `OrderItem` não existem ainda, pois foram explicitamente excluídos desta etapa.

**Impacto:** os métodos podem permanecer declarados, mas não devem ser executados até que essas entidades sejam implementadas na etapa correspondente.

---

### 2.6. Seeders iniciais

**Resolvido para os dados globais:** foram criados `AdminSeeder` e
`StoreSettingSeeder`, ambos registrados em `DatabaseSeeder`.

Categorias ainda não possuem seeder porque seu catálogo inicial não foi
definido como parte desta etapa. Isso não bloqueia a fundação e será tratado
na Gestão de Produtos.

---

## 3. Itens intencionalmente não implementados

Os itens abaixo não são falhas da fundação; ficaram de fora por solicitação explícita de escopo:

- `orders`;
- `order_items`;
- `payments`;
- controllers;
- Form Requests;
- services;
- rotas e middleware de autenticação;
- telas do site;
- telas do painel administrativo;
- integração com gateway de pagamento;
- regras de carrinho e checkout.

---

## 4. Estado final da validação

- As migrations foram executadas com sucesso no MySQL 8.
- Uma instalação limpa das migrations foi validada em banco isolado.
- Tipos, tamanhos, nulabilidade, defaults, índices únicos e chaves estrangeiras foram conferidos.
- Todos os arquivos PHP modificados passaram no lint.
- A suíte ampliada terminou com `15 passed`.
- O banco temporário usado na validação foi removido.

## 5. Próximo passo

As pendências da Fundação de Dados estão encerradas. A próxima etapa da
ordem de execução é o Login de Administrador, sem controllers, rotas ou telas
antecipados nesta fundação.
