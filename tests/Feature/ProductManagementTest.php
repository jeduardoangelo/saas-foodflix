<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_category_seeder_creates_the_fixed_categories_idempotently(): void
    {
        (new CategorySeeder())->run();
        (new CategorySeeder())->run();

        $categories = Category::query()
            ->whereIn('slug', ['lanches', 'pizzas', 'pasteis', 'bebidas'])
            ->orderBy('display_order')
            ->get(['name', 'slug', 'display_order'])
            ->map(function (Category $category) {
                return $category->toArray();
            })
            ->all();

        $this->assertSame([
            ['name' => 'Lanches', 'slug' => 'lanches', 'display_order' => 1],
            ['name' => 'Pizzas', 'slug' => 'pizzas', 'display_order' => 2],
            ['name' => 'Pastéis', 'slug' => 'pasteis', 'display_order' => 3],
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'display_order' => 4],
        ], $categories);
    }

    public function test_product_routes_require_an_authenticated_admin(): void
    {
        $category = $this->createCategory('Protegida');
        $product = $this->createProduct($category, 'Produto Protegido');

        $requests = [
            ['get', '/admin/products'],
            ['get', '/admin/products/create'],
            ['post', '/admin/products'],
            ['get', "/admin/products/{$product->id}/edit"],
            ['put', "/admin/products/{$product->id}"],
            ['patch', "/admin/products/{$product->id}/toggle"],
            ['delete', "/admin/products/{$product->id}"],
        ];

        foreach ($requests as [$method, $uri]) {
            $this->{$method}($uri)->assertRedirect('/admin/login');
        }
    }

    public function test_product_list_displays_expected_columns_and_non_deleted_products(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory('Listagem');
        $visibleProduct = $this->createProduct($category, 'Produto Visível', '25.90');
        $deletedProduct = $this->createProduct($category, 'Produto Excluído');
        $deletedProduct->delete();

        $response = $this->actingAs($admin, 'admin')->get('/admin/products');

        $response->assertOk();
        $response->assertSee('Gerenciamento de Produtos');
        $response->assertSee('Adicionar Novo Produto');
        $response->assertSeeInOrder(['Imagem', 'Nome', 'Categoria', 'Preço', 'Status', 'Ações']);
        $response->assertSee($visibleProduct->name);
        $response->assertSee($category->name);
        $response->assertSee('R$ 25,90');
        $response->assertDontSee($deletedProduct->name);
    }

    public function test_admin_can_create_an_active_product_with_one_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = $this->createCategory('Cadastro');

        $response = $this->actingAs($admin, 'admin')->post('/admin/products', [
            'name' => 'X-Burger Especial',
            'category_id' => $category->id,
            'description' => 'Hambúrguer artesanal.',
            'ingredients' => 'Pão, carne e queijo.',
            'price' => '29.90',
            'image' => UploadedFile::fake()->image('x-burger.png'),
            'active' => '1',
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success', 'Produto cadastrado com sucesso.');

        $product = Product::where('name', 'X-Burger Especial')->firstOrFail();
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame('29.90', $product->price);
        $this->assertTrue($product->active);
        $this->assertNotNull($product->image_url);
        Storage::disk('public')->assertExists($product->image_url);
    }

    public function test_product_creation_rejects_missing_category_and_non_positive_price(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'name' => '',
                'category_id' => 999999,
                'price' => '0',
                'active' => '1',
            ]);

        $response->assertRedirect('/admin/products/create');
        $response->assertSessionHasErrors(['name', 'category_id', 'price']);
        $this->assertSame(0, Product::where('category_id', 999999)->count());
    }

    public function test_admin_can_edit_a_product(): void
    {
        $admin = $this->createAdmin();
        $oldCategory = $this->createCategory('Categoria Antiga');
        $newCategory = $this->createCategory('Categoria Nova');
        $product = $this->createProduct($oldCategory, 'Nome Antigo', '10.00');

        $this->actingAs($admin, 'admin')
            ->get("/admin/products/{$product->id}/edit")
            ->assertOk()
            ->assertSee('Editar Produto')
            ->assertSee('Nome Antigo');

        $response = $this->actingAs($admin, 'admin')->put("/admin/products/{$product->id}", [
            'name' => 'Nome Atualizado',
            'category_id' => $newCategory->id,
            'description' => 'Descrição atualizada.',
            'ingredients' => 'Ingredientes atualizados.',
            'price' => '18.50',
            'active' => '0',
        ]);

        $response->assertRedirect('/admin/products');
        $product->refresh();
        $this->assertSame('Nome Atualizado', $product->name);
        $this->assertSame($newCategory->id, $product->category_id);
        $this->assertSame('18.50', $product->price);
        $this->assertFalse($product->active);
    }

    public function test_replacing_a_product_image_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = $this->createCategory('Imagem');
        $oldImage = UploadedFile::fake()->image('old.png')->store('products', 'public');
        $product = $this->createProduct($category, 'Produto com Imagem');
        $product->update(['image_url' => $oldImage]);

        $this->actingAs($admin, 'admin')->put("/admin/products/{$product->id}", [
            'name' => $product->name,
            'category_id' => $category->id,
            'price' => '12.00',
            'image' => UploadedFile::fake()->image('new.png'),
            'active' => '1',
        ])->assertRedirect('/admin/products');

        $product->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image_url);
    }

    public function test_admin_can_toggle_product_status_without_deleting_it(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory('Status');
        $product = $this->createProduct($category, 'Produto Ativo');

        $this->actingAs($admin, 'admin')
            ->patch("/admin/products/{$product->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($product->fresh()->active);
        $this->assertNull($product->fresh()->deleted_at);

        $this->actingAs($admin, 'admin')->patch("/admin/products/{$product->id}/toggle");
        $this->assertTrue($product->fresh()->active);
    }

    public function test_product_deletion_is_always_a_soft_delete(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory('Exclusão');
        $product = $this->createProduct($category, 'Produto para Excluir');

        $response = $this->actingAs($admin, 'admin')
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNotNull(Product::withTrashed()->findOrFail($product->id)->deleted_at);
    }

    public function test_authenticated_client_still_cannot_manage_products(): void
    {
        $user = User::create([
            'name' => 'Cliente sem acesso',
            'cpf' => '390.533.447-05',
            'email' => 'client-products@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/products')
            ->assertRedirect('/admin/login');
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Administrador de Produtos',
            'email' => uniqid('admin-products-', true).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function createCategory(string $name): Category
    {
        $suffix = uniqid('', true);

        return Category::create([
            'name' => $name.' '.$suffix,
            'slug' => 'category-'.str_replace('.', '-', $suffix),
            'display_order' => 99,
        ]);
    }

    private function createProduct(Category $category, string $name, string $price = '12.00'): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'price' => $price,
            'active' => true,
        ]);
    }
}
