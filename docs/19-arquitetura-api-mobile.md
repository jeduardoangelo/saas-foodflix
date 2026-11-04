Etapa nova: "API Mobile" (documento 19-arquitetura-api-mobile.md).

Implemente a API descrita nesse documento, para consumo futuro por um
app Flutter separado (repositório próprio, não faz parte deste
projeto).

Cubra:
1. Guard "api" usando tymon/jwt-auth, apontando para o model User
   (mesma tabela do guard web — cliente usa a mesma conta em site e
   app).
2. Rotas em routes/api.php, todas sob prefixo /api/v1/, controllers em
   app/Http/Controllers/Api/.
3. Endpoints de autenticação: register, login, logout, refresh,
   forgot-password, reset-password — reaproveitando os Services já
   existentes (não duplique regra de negócio: os Services de
   Cart/Order/Payment já implementados para o site continuam sendo a
   fonte de verdade).
4. Endpoints de catálogo (categories, products, products/{id}) —
   públicos, sem autenticação, respeitando o scope Product::active().
5. Endpoints de endereços (CRUD, exceto delete que não está na spec) —
   autenticados, autorização via Policy já existente.
6. Endpoint de checkout (POST /orders) — recebe os itens do carrinho no
   payload (carrinho é client-side, não há endpoint de carrinho
   dedicado, conforme a decisão do documento 06), cria o pedido via
   Services/Order e Services/Payment já existentes.
7. Endpoints de pedidos: GET /orders (paginado, Meus Pedidos) e GET
   /orders/{id} (detalhe/status) — autorização via Policy já existente.
8. Endpoint GET /store-settings — público, necessário pro app exibir
   taxa de entrega/horário antes do checkout.

Use Laravel API Resources (JsonResource) para toda resposta — nunca
serialize o Model direto. Formato de erro consistente em toda a API.

Siga docs/03-arquitetura-tecnica.md: controllers finos, nenhuma regra
de negócio nova nos controllers de API — tudo que já existe em
Services/ é reaproveitado, só a camada de Controller+Resource é nova.

Ao final, rode os testes (incluindo autenticação JWT, refresh de token
expirado, e autorização entre clientes diferentes nos endpoints de
pedidos/endereços) e me diga a URL base + um exemplo de chamada (ex:
curl) para eu testar o login.
