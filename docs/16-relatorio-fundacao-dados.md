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

## 2. O que ficou faltando

### 2.1. Garantia rígida de singleton para `store_settings`

A tabela e o model foram criados, mas o banco ainda não impede diretamente a existência de mais de uma linha.

A documentação informa que haverá uma única linha e que seu ID será `1` na prática, mas não define:

- valores iniciais para um seeder;
- uma constraint específica de singleton;
- o comportamento caso a linha seja excluída;
- se o model deve bloquear `create()` após o primeiro registro.

Adicionar uma coluna auxiliar ou uma regra automática sem essa definição inventaria comportamento que não está na spec.

**Pendente para decisão:** definir se o singleton será garantido por seeder + `updateOrCreate(['id' => 1])`, por uma camada de serviço ou também por constraint no banco.

---

### 2.2. Configuração definitiva do driver de autenticação

Os guards estão separados:

- `web` usa o provider `users`;
- `admin` usa o provider `admins`.

Ambos estão configurados atualmente com o driver `session`, seguindo a configuração Laravel já existente. A arquitetura também cita `tymon/jwt-auth`, mas não determina nesta etapa se os dois guards devem usar JWT nem como os tokens serão separados.

**Pendente para a etapa de autenticação:** confirmar se o site e o painel usarão sessão, JWT ou uma combinação dos dois e ajustar os drivers conforme a feature de login.

---

### 2.3. Hash automático de senha não foi implementado nos models

O documento de dívida técnica determina que senhas de clientes e administradores sejam sempre armazenadas com bcrypt ou Argon2. Nesta etapa não existem fluxos de cadastro, login, controllers, requests ou services.

O Laravel 8 também não possui o cast moderno `hashed`.

**Pendente para as features de autenticação:** aplicar `Hash::make()` antes de persistir senhas e adicionar testes que comprovem que texto puro nunca chega ao banco.

---

### 2.4. Testes específicos de schema e models

A suíte existente possui somente os dois testes de exemplo do Laravel. Ambos passaram, mas ainda não há testes automatizados próprios para:

- nomes e tipos das colunas;
- constraints únicas;
- chaves estrangeiras;
- casts dos models;
- relacionamentos Eloquent;
- soft deletes de `users` e `products`;
- separação entre os providers `users` e `admins`;
- comportamento singleton de `store_settings`.

O schema foi conferido diretamente no `information_schema` do MySQL durante esta implementação, mas essa validação ainda não está registrada como teste repetível no repositório.

**Pendente recomendado:** criar testes de integração do schema e testes unitários dos models antes das próximas features dependerem dessas entidades.

---

### 2.5. Relacionamentos que apontam para entidades futuras

Os models possuem os relacionamentos previstos na documentação:

- `User::orders()`;
- `Product::orderItems()`.

Os models `Order` e `OrderItem` não existem ainda, pois foram explicitamente excluídos desta etapa.

**Impacto:** os métodos podem permanecer declarados, mas não devem ser executados até que essas entidades sejam implementadas na etapa correspondente.

---

### 2.6. Seeder inicial

Não foram criados registros iniciais para:

- categorias;
- administrador;
- configurações da loja.

Isso não foi solicitado na etapa de fundação e a documentação não fornece credenciais nem todos os valores concretos necessários.

**Pendente:** criar seeders quando os dados iniciais e a estratégia segura para a senha do primeiro administrador forem definidos.

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
- A suíte atual terminou com `2 passed`.
- O banco temporário usado na validação foi removido.

## 5. Próximas decisões recomendadas

Antes da implementação do login de administrador:

1. Corrigir definitivamente as credenciais do Docker para o MySQL iniciar sem overrides.
2. Confirmar o driver dos guards: sessão ou JWT.
3. Definir como será criado o primeiro administrador sem versionar uma senha real.
4. Definir a estratégia de singleton de `store_settings`.
5. Adicionar testes automatizados para o schema e os relacionamentos já criados.
