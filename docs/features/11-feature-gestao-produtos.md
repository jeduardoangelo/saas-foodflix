# Feature: Gestão de Produtos (Admin)

> Documento 11 de N. Parte do MVP (documento 1).

## Objetivo
Permitir que o administrador mantenha o catálogo do site atualizado — cadastrar novos itens, ajustar preços e ativar/desativar produtos, com prevenção de erro (princípio de UX, documento 1).

## Papéis envolvidos
- Administrador/Operador (persona 2, documento 1)

## Fluxo principal
1. Administrador acessa "Produtos" no menu do painel
2. Vê tabela com todos os produtos: imagem, nome, categoria, preço, status (Ativo/Inativo), ações (editar/excluir)
3. Clica "Adicionar Novo Produto" para cadastrar um item novo (nome, categoria, descrição, ingredientes, preço, imagem)
4. Clica no ícone de edição para alterar dados de um produto existente
5. Usa o toggle de status para ativar/desativar um produto sem excluí-lo
6. Clica no ícone de exclusão para remover um produto (soft delete, documento 2)

## Telas
- `admin-produtos.png` — Gerenciamento de Produtos (tabela + botão "Adicionar Novo Produto")

## Regras de negócio
- Desativar um produto (`active = false`) o remove do catálogo do site imediatamente, sem apagar histórico de pedidos que já o contêm (os pedidos usam snapshot, documento 2 — não são afetados)
- Excluir um produto é soft delete (`deleted_at`, documento 2) — nunca exclusão física, para preservar integridade de pedidos antigos que referenciam esse `product_id`
- Preço é sempre positivo, maior que zero
- Categoria é obrigatória e deve existir previamente em `categories`

## Critérios de aceite
- [ ] Novo produto cadastrado aparece imediatamente no catálogo do site (se `active = true`)
- [ ] Editar um produto reflete a mudança no site sem afetar pedidos já feitos com o preço/nome anterior
- [ ] Toggle de status ativa/desativa a visibilidade do produto no site sem excluir o registro
- [ ] Exclusão de produto não quebra a exibição de pedidos antigos que o contêm
- [ ] Tentativa de salvar produto sem categoria ou com preço inválido é rejeitada com mensagem clara

## Fora de escopo
- Gestão de categorias pela interface (criar/editar/remover categoria) — no MVP, categorias são fixas/seed inicial (Lanches, Pizzas, Pastéis, Bebidas), sem CRUD dedicado
- Upload de múltiplas imagens por produto (galeria) — apenas uma imagem principal
- Controle de estoque/quantidade disponível
