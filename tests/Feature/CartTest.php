<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cart_is_public_and_has_an_empty_state(): void
    {
        $this->assertGuest('web');

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Meu Carrinho')
            ->assertSee('Seu carrinho está vazio')
            ->assertSee('Ver Cardápio');
    }

    public function test_guest_can_add_one_product_from_a_catalog_card(): void
    {
        $product = $this->createProduct('Produto do Card', '12.50');

        $response = $this->from('/')->post("/cart/items/{$product->id}");

        $response->assertRedirect('/');
        $response->assertSessionHas("cart.items.{$product->id}", 1);
        $this->assertGuest('web');

        $this->get('/')
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Meu Carrinho')
            ->assertSee('Produto adicionado ao carrinho.');
    }

    public function test_guest_can_add_the_quantity_selected_on_product_detail(): void
    {
        $product = $this->createProduct('Produto Detalhado', '18.90');

        $this->from("/products/{$product->id}")
            ->post("/cart/items/{$product->id}", ['quantity' => 4])
            ->assertRedirect("/products/{$product->id}")
            ->assertSessionHas("cart.items.{$product->id}", 4);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('R$ 75,60');
    }

    public function test_adding_the_same_product_accumulates_quantity_up_to_the_limit(): void
    {
        $product = $this->createProduct('Produto Repetido', '10.00');

        $this->post("/cart/items/{$product->id}", ['quantity' => 40]);
        $this->post("/cart/items/{$product->id}", ['quantity' => 70])
            ->assertSessionHas("cart.items.{$product->id}", 99);
    }

    public function test_inactive_or_deleted_products_cannot_be_added(): void
    {
        $inactive = $this->createProduct('Produto Inativo', '10.00', false);
        $deleted = $this->createProduct('Produto Excluído', '10.00');
        $deleted->delete();

        $this->post("/cart/items/{$inactive->id}", ['quantity' => 1])->assertNotFound();
        $this->post("/cart/items/{$deleted->id}", ['quantity' => 1])->assertNotFound();
        $this->assertEmpty(session('cart.items', []));
    }

    public function test_add_and_update_validate_quantity(): void
    {
        $product = $this->createProduct('Produto Validado', '10.00');

        $this->from('/')->post("/cart/items/{$product->id}", ['quantity' => 0])
            ->assertRedirect('/')
            ->assertSessionHasErrors('quantity');

        $this->post("/cart/items/{$product->id}", ['quantity' => 1]);

        $this->from('/cart')->patch("/cart/items/{$product->id}", ['quantity' => 100])
            ->assertRedirect('/cart')
            ->assertSessionHasErrors('quantity');
    }

    public function test_cart_page_calculates_item_subtotals_subtotal_and_total(): void
    {
        $first = $this->createProduct('Primeiro Produto', '10.50');
        $second = $this->createProduct('Segundo Produto', '5.25');

        $this->post("/cart/items/{$first->id}", ['quantity' => 2]);
        $this->post("/cart/items/{$second->id}", ['quantity' => 3]);

        $response = $this->get('/cart');

        $response->assertOk();
        $response->assertSee($first->name);
        $response->assertSee($second->name);
        $response->assertSee('R$ 21,00');
        $response->assertSee('R$ 15,75');
        $response->assertSee('R$ 36,75');
        $response->assertSee('Taxa de entrega');
        $response->assertSee('A calcular');
        $response->assertSee('Ir para o Pagamento');
        $response->assertSee('Continuar Comprando');
    }

    public function test_customer_can_update_and_reduce_quantity_to_zero_to_remove_item(): void
    {
        $product = $this->createProduct('Produto Ajustável', '8.00');
        $this->post("/cart/items/{$product->id}", ['quantity' => 2]);

        $this->from('/cart')->patch("/cart/items/{$product->id}", ['quantity' => 5])
            ->assertRedirect('/cart')
            ->assertSessionHas("cart.items.{$product->id}", 5);

        $this->from('/cart')->patch("/cart/items/{$product->id}", ['quantity' => 0])
            ->assertRedirect('/cart');

        $this->assertArrayNotHasKey($product->id, session('cart.items', []));
        $this->get('/cart')->assertSee('Seu carrinho está vazio');
    }

    public function test_customer_can_remove_an_item_explicitly(): void
    {
        $product = $this->createProduct('Produto Removível', '7.00');
        $this->post("/cart/items/{$product->id}", ['quantity' => 2]);

        $this->from('/cart')->delete("/cart/items/{$product->id}")
            ->assertRedirect('/cart');

        $this->assertEmpty(session('cart.items', []));
    }

    public function test_mini_cart_displays_total_unit_count_summary_and_controls(): void
    {
        $product = $this->createProduct('Produto no Mini Carrinho', '11.00');
        $this->post("/cart/items/{$product->id}", ['quantity' => 3]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<span class="cart-count">3</span>', false);
        $response->assertSee('3 itens');
        $response->assertSee('R$ 33,00');
        $response->assertSee(route('site.cart.items.update', $product), false);
        $response->assertSee(route('site.cart.items.destroy', $product), false);
        $response->assertSee(route('site.cart.index'), false);
    }

    public function test_cart_survives_navigation_in_the_same_session_and_discards_unavailable_products(): void
    {
        $product = $this->createProduct('Produto da Sessão', '9.90');
        $this->post("/cart/items/{$product->id}", ['quantity' => 2]);

        $this->get("/products/{$product->id}")
            ->assertOk()
            ->assertSee('<span class="cart-count">2</span>', false);

        $product->update(['active' => false]);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Seu carrinho está vazio');
        $this->assertEmpty(session('cart.items', []));
    }

    private function createProduct(string $name, string $price, bool $active = true): Product
    {
        $category = Category::create([
            'name' => 'Categoria '.uniqid(),
            'slug' => 'categoria-'.uniqid(),
            'display_order' => 100,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Descrição do produto para teste.',
            'ingredients' => 'Ingrediente um, ingrediente dois.',
            'price' => $price,
            'active' => $active,
        ]);
    }
}
