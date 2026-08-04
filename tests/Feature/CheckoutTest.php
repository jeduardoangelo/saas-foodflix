<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\LoggingPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use LogicException;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_to_client_login_before_checkout(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
        $this->post('/checkout')->assertRedirect('/login');
    }

    public function test_checkout_requires_a_non_empty_cart(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->get('/checkout')
            ->assertRedirect('/cart')
            ->assertSessionHas('cart_message', 'Seu carrinho está vazio. Adicione produtos antes de finalizar.');
    }

    public function test_checkout_page_prefills_default_address_and_shows_delivery_totals(): void
    {
        $this->prepareStoreSettings('7.50');
        $user = $this->createUser();
        $product = $this->createProduct('Produto no Checkout', '20.00');

        $response = $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 2]])
            ->get('/checkout');

        $response->assertOk();
        $response->assertSee('Finalizar Pedido');
        $response->assertSee('Rua do Cliente');
        $response->assertSee('Como você quer receber?');
        $response->assertSee('Retirar no Local');
        $response->assertSee('Cartão de Crédito');
        $response->assertSee('Pix');
        $response->assertSee('R$ 40,00');
        $response->assertSee('R$ 7,50');
        $response->assertSee('R$ 47,50');
    }

    public function test_approved_delivery_payment_creates_order_snapshots_items_and_payment_atomically(): void
    {
        $this->prepareStoreSettings('7.50');
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new FakePaymentGateway(true);
        });
        $user = $this->createUser();
        $product = $this->createProduct('Pizza Snapshot', '20.00');

        $response = $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 2]])
            ->post('/checkout', $this->deliveryPayload());

        $order = Order::firstOrFail();
        $response->assertRedirect("/checkout/success/{$order->id}");
        $this->assertSame('novo', $order->status);
        $this->assertSame('entrega', $order->delivery_method);
        $this->assertSame('cartao_credito', $order->payment_method);
        $this->assertSame('pago', $order->payment_status);
        $this->assertSame('40.00', $order->subtotal);
        $this->assertSame('7.50', $order->delivery_fee);
        $this->assertSame('47.50', $order->total);
        $this->assertEquals([
            'street' => 'Avenida Editada',
            'number' => '900',
            'neighborhood' => 'Novo Bairro',
            'city' => 'Campinas',
            'complement' => 'Bloco B',
        ], $order->delivery_address_snapshot);

        $item = $order->items()->firstOrFail();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('Pizza Snapshot', $item->product_name_snapshot);
        $this->assertSame('20.00', $item->unit_price_snapshot);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('40.00', $item->subtotal);

        $payment = $order->payment()->firstOrFail();
        $this->assertSame('fake', $payment->gateway);
        $this->assertStringStartsWith('fake_', $payment->gateway_transaction_id);
        $this->assertSame('credit_card', $payment->method);
        $this->assertSame('aprovado', $payment->status);
        $this->assertSame('47.50', $payment->amount);
        $this->assertNotNull($payment->paid_at);
        $this->assertEmpty(session('cart.items', []));
    }

    public function test_gateway_receives_an_unpersisted_order_and_success_page_is_owner_only(): void
    {
        $this->prepareStoreSettings('0.00');
        $gateway = new class implements PaymentGatewayInterface {
            public $receivedPersistedOrder;

            public function charge(Order $order): PaymentResult
            {
                $this->receivedPersistedOrder = $order->exists;

                return PaymentResult::approved('fake', 'fake_contract_test');
            }
        };
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $user = $this->createUser('cliente-checkout@example.com', '529.982.247-25');
        $otherUser = $this->createUser('outro-cliente@example.com', '111.444.777-35');
        $product = $this->createProduct('Produto Contrato', '12.00');

        $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 1]])
            ->post('/checkout', $this->pickupPayload());

        $order = Order::firstOrFail();
        $this->assertFalse($gateway->receivedPersistedOrder);
        $this->actingAs($user, 'web')->get("/checkout/success/{$order->id}")
            ->assertOk()
            ->assertSee($order->order_number);
        $this->actingAs($otherUser, 'web')->get("/checkout/success/{$order->id}")->assertForbidden();
    }

    public function test_declined_payment_logs_failure_creates_no_records_and_keeps_cart(): void
    {
        $this->prepareStoreSettings('7.50');
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new LoggingPaymentGateway(new FakePaymentGateway(false));
        });
        $paymentLogger = Mockery::mock(LoggerInterface::class);
        $paymentLogger->shouldReceive('error')->once()->withArgs(function ($message, $context) {
            return $message === 'Falha na cobrança do pedido.'
                && array_keys($context) === ['order_id', 'order_number', 'gateway', 'message']
                && $context['order_id'] === null
                && strpos($context['order_number'], 'FF') === 0
                && $context['gateway'] === 'fake'
                && $context['message'] === 'O pagamento foi recusado. Revise a forma de pagamento e tente novamente.';
        });
        Log::shouldReceive('channel')->once()->with('payments')->andReturn($paymentLogger);
        $user = $this->createUser();
        $product = $this->createProduct('Produto Recusado', '25.00');

        $response = $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 2]])
            ->from('/checkout')
            ->post('/checkout', $this->deliveryPayload());

        $response->assertRedirect('/checkout');
        $response->assertSessionHasErrors([
            'payment_method' => 'O pagamento foi recusado. Revise a forma de pagamento e tente novamente.',
        ]);
        $response->assertSessionHas("cart.items.{$product->id}", 2);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(0, Payment::count());
    }

    public function test_pickup_has_no_address_snapshot_or_delivery_fee(): void
    {
        $this->prepareStoreSettings('9.90');
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new FakePaymentGateway(true);
        });
        $user = $this->createUser();
        $product = $this->createProduct('Pedido Retirada', '30.00');

        $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 1]])
            ->post('/checkout', $this->pickupPayload())
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('retirada', $order->delivery_method);
        $this->assertNull($order->delivery_address_snapshot);
        $this->assertSame('0.00', $order->delivery_fee);
        $this->assertSame('30.00', $order->total);
        $this->assertSame('pix', $order->payment->method);
    }

    public function test_delivery_requires_address_but_pickup_does_not(): void
    {
        $this->prepareStoreSettings('5.00');
        $user = $this->createUser();
        $product = $this->createProduct('Produto Validado Checkout', '15.00');

        $response = $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 1]])
            ->from('/checkout')
            ->post('/checkout', [
                'delivery_method' => 'entrega',
                'payment_method' => 'pix',
            ]);

        $response->assertRedirect('/checkout');
        $response->assertSessionHasErrors(['street', 'number', 'neighborhood', 'city']);
        $this->assertSame(0, Order::count());
    }

    public function test_product_and_address_snapshots_cannot_be_changed_after_creation(): void
    {
        $this->prepareStoreSettings('5.00');
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new FakePaymentGateway(true);
        });
        $user = $this->createUser();
        $product = $this->createProduct('Produto Imutável', '15.00');
        $this->actingAs($user, 'web')
            ->withSession(['cart.items' => [$product->id => 1]])
            ->post('/checkout', $this->deliveryPayload());

        $order = Order::firstOrFail();
        $item = $order->items()->firstOrFail();

        try {
            $order->update(['delivery_address_snapshot' => ['street' => 'Rua Alterada']]);
            $this->fail('O snapshot de endereço deveria ser imutável.');
        } catch (LogicException $exception) {
            $this->assertSame('O snapshot do endereço do pedido é imutável.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Os snapshots do produto no item do pedido são imutáveis.');
        $item->update(['product_name_snapshot' => 'Nome Alterado']);
    }

    private function prepareStoreSettings(string $deliveryFee): void
    {
        $this->seed(StoreSettingSeeder::class);
        StoreSetting::current()->update([
            'delivery_fee' => $deliveryFee,
            'physical_address' => 'Rua da Loja, 100',
            'opening_hours' => 'Todos os dias, 18h às 23h',
        ]);
    }

    private function createUser(string $email = 'cliente-checkout@example.com', string $cpf = '529.982.247-25'): User
    {
        $user = User::create([
            'name' => 'Cliente Checkout',
            'cpf' => $cpf,
            'email' => $email,
            'password' => 'senha-segura',
        ]);

        $user->addresses()->create([
            'street' => 'Rua do Cliente',
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'complement' => 'Casa',
            'is_default' => true,
        ]);

        return $user;
    }

    private function createProduct(string $name, string $price): Product
    {
        $category = Category::create([
            'name' => 'Checkout '.uniqid(),
            'slug' => 'checkout-'.uniqid(),
            'display_order' => 100,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Produto usado no checkout.',
            'price' => $price,
            'active' => true,
        ]);
    }

    private function deliveryPayload(): array
    {
        return [
            'delivery_method' => 'entrega',
            'street' => 'Avenida Editada',
            'number' => '900',
            'neighborhood' => 'Novo Bairro',
            'city' => 'Campinas',
            'complement' => 'Bloco B',
            'payment_method' => 'cartao_credito',
        ];
    }

    private function pickupPayload(): array
    {
        return [
            'delivery_method' => 'retirada',
            'payment_method' => 'pix',
        ];
    }
}
