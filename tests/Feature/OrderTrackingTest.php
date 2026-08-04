<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_detail_requires_customer_authentication(): void
    {
        $user = $this->createUser('cliente-status@example.com', '529.982.247-25');
        $order = $this->createOrder($user);

        $this->get("/orders/{$order->id}")->assertRedirect('/login');
    }

    public function test_owner_sees_complete_order_information_and_address_snapshot(): void
    {
        $user = $this->createUser('cliente-status@example.com', '529.982.247-25');
        $user->addresses()->create([
            'street' => 'Endereço Atual', 'number' => '999', 'neighborhood' => 'Bairro Atual',
            'city' => 'Cidade Atual', 'is_default' => true,
        ]);
        $order = $this->createOrder($user);

        $response = $this->actingAs($user, 'web')->get("/orders/{$order->id}");

        $response->assertOk();
        $response->assertSee('Pedido #'.$order->order_number);
        $response->assertSee('Acompanhe seu pedido');
        $response->assertSee('Novo');
        $response->assertSee('Em Preparo');
        $response->assertSee('Saiu para Entrega');
        $response->assertSee('Concluído');
        $response->assertSee('Pizza Snapshot Cliente');
        $response->assertSee('2 × R$ 20,00');
        $response->assertSee('R$ 40,00');
        $response->assertSee('R$ 5,00');
        $response->assertSee('R$ 45,00');
        $response->assertSee('Cartão de crédito');
        $response->assertSee('Entrega');
        $response->assertSee('Rua Histórica, 123');
        $response->assertDontSee('Endereço Atual');
    }

    public function test_customer_cannot_access_another_customers_order_by_changing_url_id(): void
    {
        $owner = $this->createUser('dono-pedido@example.com', '529.982.247-25');
        $intruder = $this->createUser('outro-cliente@example.com', '111.444.777-35');
        $order = $this->createOrder($owner);

        $this->actingAs($intruder, 'web')
            ->get("/orders/{$order->id}")
            ->assertForbidden();
    }

    public function test_status_is_read_fresh_from_orders_on_every_request(): void
    {
        $user = $this->createUser('cliente-status@example.com', '529.982.247-25');
        $order = $this->createOrder($user);
        $expectedStatuses = [
            'novo' => 'Novo',
            'em_preparo' => 'Em Preparo',
            'saiu_para_entrega' => 'Saiu para Entrega',
            'concluido' => 'Concluído',
        ];

        foreach ($expectedStatuses as $status => $label) {
            $order->update(['status' => $status]);

            $this->actingAs($user, 'web')
                ->get("/orders/{$order->id}")
                ->assertOk()
                ->assertSee('data-current-status="'.$status.'"', false)
                ->assertSee('<span class="current-status">'.$label.'</span>', false);
        }
    }

    public function test_checkout_confirmation_links_directly_to_order_tracking(): void
    {
        $user = $this->createUser('cliente-status@example.com', '529.982.247-25');
        $order = $this->createOrder($user);

        $this->actingAs($user, 'web')
            ->get("/checkout/success/{$order->id}")
            ->assertOk()
            ->assertSee('Acompanhar Pedido')
            ->assertSee(route('site.orders.show', $order), false);
    }

    private function createUser(string $email, string $cpf): User
    {
        return User::create([
            'name' => 'Cliente Acompanhamento',
            'cpf' => $cpf,
            'email' => $email,
            'password' => 'senha-segura',
        ]);
    }

    private function createOrder(User $user): Order
    {
        $category = Category::create([
            'name' => 'Status '.uniqid(),
            'slug' => 'status-'.uniqid(),
            'display_order' => 100,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produto Atual',
            'price' => '20.00',
            'active' => true,
        ]);
        $order = Order::create([
            'order_number' => 'ST'.strtoupper(uniqid()),
            'user_id' => $user->id,
            'status' => 'novo',
            'delivery_method' => 'entrega',
            'delivery_address_snapshot' => [
                'street' => 'Rua Histórica', 'number' => '123', 'neighborhood' => 'Centro',
                'city' => 'São Paulo', 'complement' => 'Casa 1',
            ],
            'payment_method' => 'cartao_credito',
            'payment_status' => 'pago',
            'subtotal' => '40.00',
            'delivery_fee' => '5.00',
            'total' => '45.00',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => 'Pizza Snapshot Cliente',
            'unit_price_snapshot' => '20.00',
            'quantity' => 2,
            'subtotal' => '40.00',
        ]);
        $order->payment()->create([
            'gateway' => 'fake',
            'gateway_transaction_id' => 'fake_status_test',
            'method' => 'credit_card',
            'status' => 'aprovado',
            'amount' => '45.00',
            'paid_at' => now(),
        ]);

        return $order;
    }
}
