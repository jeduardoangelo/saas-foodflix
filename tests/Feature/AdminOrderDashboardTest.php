<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrderDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-08-04 14:30:00', config('app.timezone')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_order_dashboard_routes_require_admin_authentication(): void
    {
        $this->get('/admin/orders')->assertRedirect('/admin/login');
        $this->patch('/admin/orders/1/advance')->assertRedirect('/admin/login');
    }

    public function test_summary_cards_use_only_current_day_orders_and_paid_revenue(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $this->createOrder($user, $product, 'novo', 'pago', '100.00');
        $this->createOrder($user, $product, 'em_preparo', 'pendente', '50.00');
        $this->createOrder($user, $product, 'saiu_para_entrega', 'pago', '70.00');
        $this->createOrder($user, $product, 'concluido', 'pago', '200.00');
        $this->createOrder($user, $product, 'novo', 'pago', '1000.00', now()->subDay());

        $response = $this->actingAs($this->createAdmin(), 'admin')->get('/admin/orders');

        $response->assertOk();
        $response->assertSee('<strong data-metric="new_orders">1</strong>', false);
        $response->assertSee('<strong data-metric="revenue_cents">R$ 370,00</strong>', false);
        $response->assertSee('<strong data-metric="completed_orders">1</strong>', false);
        $response->assertSee('<strong data-metric="in_progress_orders">2</strong>', false);
        $response->assertDontSee('R$ 1.000,00');
    }

    public function test_kanban_distributes_today_orders_and_displays_card_information(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('Pizza do Kanban');
        $new = $this->createOrder($user, $product, 'novo', 'pago', '42.50');
        $preparing = $this->createOrder($user, $product, 'em_preparo', 'pago', '42.50');
        $shipping = $this->createOrder($user, $product, 'saiu_para_entrega', 'pago', '42.50');
        $completed = $this->createOrder($user, $product, 'concluido', 'pago', '42.50');

        $response = $this->actingAs($this->createAdmin(), 'admin')->get('/admin/orders');

        $response->assertOk();
        $response->assertSee('Novos Pedidos');
        $response->assertSee('Em Preparo');
        $response->assertSee('Saiu para Entrega');
        $response->assertSee('Concluídos');
        foreach ([$new, $preparing, $shipping, $completed] as $order) {
            $response->assertSee('#'.$order->order_number);
        }
        $response->assertSee('Cliente do Kanban');
        $response->assertSee('2× Pizza do Kanban');
        $response->assertSee('R$ 42,50');
        $response->assertSee('Ver Detalhes');
    }

    public function test_modal_uses_order_address_snapshot_and_displays_complete_details(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('Lanche Snapshot');
        $order = $this->createOrder($user, $product, 'novo', 'pago', '42.50');
        $user->addresses()->create([
            'street' => 'Rua Atual do Cliente', 'number' => '999', 'neighborhood' => 'Atual',
            'city' => 'Cidade Atual', 'is_default' => true,
        ]);

        $response = $this->actingAs($this->createAdmin(), 'admin')->get('/admin/orders');

        $response->assertSee('Detalhes do Pedido #'.$order->order_number);
        $response->assertSee('cliente-kanban@example.com');
        $response->assertSee('529.982.247-25');
        $response->assertSee('Rua do Snapshot, 123');
        $response->assertDontSee('Rua Atual do Cliente');
        $response->assertSee('Lanche Snapshot');
        $response->assertSee('Subtotal');
        $response->assertSee('Taxa de entrega');
        $response->assertSee('Cartão de crédito');
    }

    public function test_order_advances_one_status_at_a_time_and_cannot_advance_after_completion(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $order = $this->createOrder($user, $product, 'novo', 'pago', '42.50');
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->patchJson("/admin/orders/{$order->id}/advance", ['status' => 'concluido'])
            ->assertOk()
            ->assertJsonPath('status', 'em_preparo')
            ->assertJsonPath('metrics.new_orders', 0)
            ->assertJsonPath('metrics.in_progress_orders', 1);
        $this->assertSame('em_preparo', $order->fresh()->status);

        $this->actingAs($admin, 'admin')->patchJson("/admin/orders/{$order->id}/advance")
            ->assertOk()->assertJsonPath('status', 'saiu_para_entrega');
        $this->actingAs($admin, 'admin')->patchJson("/admin/orders/{$order->id}/advance")
            ->assertOk()->assertJsonPath('status', 'concluido')
            ->assertJsonPath('next_status', null);

        $this->actingAs($admin, 'admin')->patchJson("/admin/orders/{$order->id}/advance")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Este pedido já está concluído e não pode mais avançar.');
    }

    public function test_approved_checkout_order_appears_immediately_in_new_orders_column(): void
    {
        $this->seed(StoreSettingSeeder::class);
        StoreSetting::current()->update(['delivery_fee' => '5.00']);
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new FakePaymentGateway(true);
        });
        $user = $this->createUser();
        $user->addresses()->create([
            'street' => 'Rua Checkout', 'number' => '50', 'neighborhood' => 'Centro',
            'city' => 'São Paulo', 'is_default' => true,
        ]);
        $product = $this->createProduct('Pedido vindo do Checkout');

        $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 1]])
            ->post('/checkout', [
                'delivery_method' => 'retirada',
                'payment_method' => 'pix',
            ])
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('novo', $order->status);

        $response = $this->actingAs($this->createAdmin(), 'admin')->get('/admin/orders');
        $response->assertOk();
        $response->assertSeeInOrder([
            'data-status="novo"',
            '#'.$order->order_number,
            'Pedido vindo do Checkout',
        ], false);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Administrador Kanban',
            'email' => 'admin-kanban@example.com',
            'password' => Hash::make('senha-admin'),
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Cliente do Kanban',
            'cpf' => '529.982.247-25',
            'email' => 'cliente-kanban@example.com',
            'password' => 'senha-cliente',
        ]);
    }

    private function createProduct(string $name = 'Produto Kanban'): Product
    {
        $category = Category::create([
            'name' => 'Kanban '.uniqid(),
            'slug' => 'kanban-'.uniqid(),
            'display_order' => 100,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'price' => '20.00',
            'active' => true,
        ]);
    }

    private function createOrder(User $user, Product $product, string $status, string $paymentStatus, string $total, $createdAt = null): Order
    {
        $order = Order::create([
            'order_number' => 'KB'.strtoupper(uniqid()),
            'user_id' => $user->id,
            'status' => $status,
            'delivery_method' => 'entrega',
            'delivery_address_snapshot' => [
                'street' => 'Rua do Snapshot', 'number' => '123', 'neighborhood' => 'Centro',
                'city' => 'São Paulo', 'complement' => 'Casa 2',
            ],
            'payment_method' => 'cartao_credito',
            'payment_status' => $paymentStatus,
            'subtotal' => '40.00',
            'delivery_fee' => '2.50',
            'total' => $total,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'unit_price_snapshot' => '20.00',
            'quantity' => 2,
            'subtotal' => '40.00',
        ]);

        if ($createdAt) {
            $order->timestamps = false;
            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;
            $order->save();
            $order->timestamps = true;
        }

        return $order;
    }
}
