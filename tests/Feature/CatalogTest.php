<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_is_public_and_displays_the_static_promotion(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Foodflix');
        $response->assertSee('Seu pedido favorito, do seu jeito.');
        $response->assertSee('Os Mais Pedidos', false);
        $this->assertGuest('web');
    }

    public function test_home_groups_only_active_non_deleted_products_by_category(): void
    {
        $lanches = $this->createCategory('Lanches Catálogo', 1);
        $pizzas = $this->createCategory('Pizzas Catálogo', 2);
        $activeSnack = $this->createProduct($lanches, 'Lanche Ativo Catálogo');
        $activePizza = $this->createProduct($pizzas, 'Pizza Ativa Catálogo');
        $inactive = $this->createProduct($lanches, 'Lanche Inativo Catálogo', false);
        $deleted = $this->createProduct($pizzas, 'Pizza Excluída Catálogo');
        $deleted->delete();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder([
            $lanches->name,
            $activeSnack->name,
            $pizzas->name,
            $activePizza->name,
        ]);
        $response->assertDontSee($inactive->name);
        $response->assertDontSee($deleted->name);
    }

    public function test_product_name_search_is_case_insensitive_and_hides_inactive_products(): void
    {
        $category = $this->createCategory('Busca');
        $matching = $this->createProduct($category, 'Pizza Portuguesa Especial');
        $nonMatching = $this->createProduct($category, 'X-Burger Tradicional');
        $inactiveMatching = $this->createProduct($category, 'PIZZA Inativa', false);

        $response = $this->get('/?q=pIzZa');

        $response->assertOk();
        $response->assertSee('Resultados para “pizza”');
        $response->assertSee($matching->name);
        $response->assertDontSee($nonMatching->name);
        $response->assertDontSee($inactiveMatching->name);
    }

    public function test_featured_products_follow_the_manual_configuration_order(): void
    {
        $category = $this->createCategory('Destaques');
        $first = $this->createProduct($category, 'Primeiro Destaque Manual');
        $second = $this->createProduct($category, 'Segundo Destaque Manual');
        $inactive = $this->createProduct($category, 'Destaque Manual Inativo', false);

        config()->set('catalog.featured_product_ids', [
            $second->id,
            $inactive->id,
            $first->id,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Os Mais Pedidos');
        $response->assertSeeInOrder([$second->name, $first->name]);
        $response->assertDontSee($inactive->name);
    }

    public function test_product_detail_displays_all_catalog_fields_and_quantity_controls(): void
    {
        $category = $this->createCategory('Detalhes');
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produto Completo de Teste',
            'description' => 'Descrição detalhada do produto.',
            'ingredients' => 'Ingrediente A, Ingrediente B e Ingrediente C.',
            'price' => '37.40',
            'active' => true,
        ]);

        $response = $this->get("/products/{$product->id}");

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($category->name);
        $response->assertSee($product->description);
        $response->assertSee($product->ingredients);
        $response->assertSee('R$ 37,40');
        $response->assertSee('data-quantity-minus', false);
        $response->assertSee('data-quantity-plus', false);
        $response->assertSee('Adicionar ao Carrinho');
    }

    public function test_inactive_and_soft_deleted_products_return_not_found_on_detail(): void
    {
        $category = $this->createCategory('Detalhe Protegido');
        $inactive = $this->createProduct($category, 'Detalhe Inativo', false);
        $deleted = $this->createProduct($category, 'Detalhe Excluído');
        $deleted->delete();

        $this->get("/products/{$inactive->id}")->assertNotFound();
        $this->get("/products/{$deleted->id}")->assertNotFound();
    }

    public function test_product_cards_expose_details_and_non_persistent_cart_actions(): void
    {
        $category = $this->createCategory('Ações');
        $product = $this->createProduct($category, 'Produto com Ações');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('site.products.show', $product), false);
        $response->assertSee('Ver Detalhes');
        $response->assertSee('Pedir');
        $response->assertSee('O carrinho será habilitado na próxima etapa.');
    }

    public function test_active_scope_returns_only_active_non_deleted_products(): void
    {
        $category = $this->createCategory('Scope');
        $active = $this->createProduct($category, 'Scope Ativo');
        $this->createProduct($category, 'Scope Inativo', false);
        $deleted = $this->createProduct($category, 'Scope Excluído');
        $deleted->delete();

        $ids = Product::query()
            ->active()
            ->whereIn('name', ['Scope Ativo', 'Scope Inativo', 'Scope Excluído'])
            ->pluck('id')
            ->all();

        $this->assertSame([$active->id], $ids);
    }

    private function createCategory(string $name, int $displayOrder = 50): Category
    {
        $suffix = uniqid('', true);

        return Category::create([
            'name' => $name.' '.$suffix,
            'slug' => 'catalog-'.str_replace('.', '-', $suffix),
            'display_order' => $displayOrder,
        ]);
    }

    private function createProduct(Category $category, string $name, bool $active = true): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Descrição de '.$name,
            'ingredients' => 'Ingredientes de '.$name,
            'price' => '19.90',
            'active' => $active,
        ]);
    }
}
