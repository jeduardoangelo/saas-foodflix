<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\JWT;
use Tests\TestCase;

class ApiMobileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_register_login_logout_and_use_the_same_account_as_web(): void
    {
        $registration = $this->postJson('/api/v1/register', $this->registrationPayload());

        $registration->assertCreated()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user' => ['id', 'email', 'addresses']])
            ->assertJsonPath('user.email', 'mobile@example.com');

        $user = User::where('email', 'mobile@example.com')->firstOrFail();
        $this->assertTrue(auth('web')->validate([
            'email' => $user->email,
            'password' => 'senha-segura',
        ]));

        $login = $this->postJson('/api/v1/login', [
            'email' => 'MOBILE@EXAMPLE.COM',
            'password' => 'senha-segura',
        ])->assertOk()->assertJsonPath('user.id', $user->id);

        $this->withToken($login->json('access_token'))
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Sessão encerrada com sucesso.');
    }

    public function test_invalid_login_and_missing_token_use_the_consistent_error_format(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_credentials')
            ->assertJsonStructure(['error' => ['code', 'message', 'details']]);

        $this->getJson('/api/v1/orders')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_an_expired_access_token_can_be_refreshed_inside_the_refresh_window(): void
    {
        $user = $this->createUser('refresh@example.com', '529.982.247-25');
        Carbon::setTestNow(now());
        $jwt = app(JWT::class);
        $jwt->factory()->setTTL(1);
        $expiredToken = $jwt->fromUser($user);
        $jwt->factory()->setTTL(60);
        Carbon::setTestNow(now()->addMinutes(2));

        $this->withToken($expiredToken)
            ->getJson('/api/v1/orders')
            ->assertUnauthorized();

        $refresh = $this->withToken($expiredToken)
            ->postJson('/api/v1/refresh')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->withToken($refresh->json('access_token'))
            ->getJson('/api/v1/orders')
            ->assertOk();

        Carbon::setTestNow();
    }

    public function test_catalog_and_store_settings_are_public_and_hide_inactive_products(): void
    {
        $this->seed(StoreSettingSeeder::class);
        $category = $this->createCategory();
        $active = $this->createProduct($category, 'Produto ativo', true);
        $inactive = $this->createProduct($category, 'Produto inativo', false);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonFragment(['id' => $category->id, 'name' => $category->name]);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonFragment(['id' => $active->id, 'name' => 'Produto ativo'])
            ->assertJsonMissing(['id' => $inactive->id, 'name' => 'Produto inativo']);

        $this->getJson("/api/v1/products/{$active->id}")->assertOk();
        $this->getJson("/api/v1/products/{$inactive->id}")
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');

        $this->getJson('/api/v1/store-settings')
            ->assertOk()
            ->assertJsonStructure(['store_name', 'opening_hours', 'delivery_fee']);
    }

    public function test_address_crud_is_restricted_to_its_owner(): void
    {
        $owner = $this->createUser('address-owner@example.com', '529.982.247-25');
        $other = $this->createUser('address-other@example.com', '111.444.777-35');
        $address = $owner->addresses()->create($this->addressPayload());

        $this->withToken($this->tokenFor($owner))
            ->getJson("/api/v1/addresses/{$address->id}")
            ->assertOk()
            ->assertJsonPath('id', $address->id);

        $otherToken = $this->tokenFor($other);
        $this->withToken($otherToken)
            ->getJson("/api/v1/addresses/{$address->id}")
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');
        $this->withToken($otherToken)
            ->putJson("/api/v1/addresses/{$address->id}", $this->addressPayload('Rua invadida'))
            ->assertForbidden();

        $created = $this->withToken($otherToken)
            ->postJson('/api/v1/addresses', $this->addressPayload('Rua nova'))
            ->assertCreated()
            ->assertJsonPath('street', 'Rua nova');

        $this->withToken($otherToken)
            ->putJson('/api/v1/addresses/'.$created->json('id'), $this->addressPayload('Rua atualizada'))
            ->assertOk()
            ->assertJsonPath('street', 'Rua atualizada');
    }

    public function test_mobile_checkout_uses_payload_items_and_creates_snapshots_and_payment(): void
    {
        $this->seed(StoreSettingSeeder::class);
        StoreSetting::current()->update(['delivery_fee' => '7.50']);
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new FakePaymentGateway(true);
        });
        $user = $this->createUser('checkout-api@example.com', '529.982.247-25');
        $product = $this->createProduct($this->createCategory(), 'Pizza mobile', true, '20.00');

        $response = $this->withToken($this->tokenFor($user))->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'delivery_method' => 'entrega',
            'street' => 'Rua Mobile',
            'number' => '42',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'payment_method' => 'pix',
        ]);

        $response->assertCreated()
            ->assertJsonPath('subtotal', '40.00')
            ->assertJsonPath('delivery_fee', '7.50')
            ->assertJsonPath('total', '47.50')
            ->assertJsonPath('items.0.name', 'Pizza mobile')
            ->assertJsonPath('payment.status', 'aprovado');

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => '47.50']);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name_snapshot' => 'Pizza mobile',
            'quantity' => 2,
        ]);
    }

    public function test_order_list_is_paginated_and_order_details_are_owner_only(): void
    {
        $owner = $this->createUser('order-owner@example.com', '529.982.247-25');
        $other = $this->createUser('order-other@example.com', '111.444.777-35');
        $order = $this->createOrder($owner);

        $this->withToken($this->tokenFor($owner))
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('data.0.id', $order->id);

        $this->withToken($this->tokenFor($owner))
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('id', $order->id);

        $this->withToken($this->tokenFor($other))
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');
    }

    public function test_password_recovery_endpoints_reuse_the_password_broker(): void
    {
        Notification::fake();
        $user = $this->createUser('password-api@example.com', '529.982.247-25');

        $this->postJson('/api/v1/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);
        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::broker('users')->createToken($user);
        $this->postJson('/api/v1/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'nova-senha-segura',
            'password_confirmation' => 'nova-senha-segura',
        ])->assertOk()->assertJsonPath('message', 'Senha redefinida com sucesso.');

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'nova-senha-segura',
        ])->assertOk();
    }

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    private function createUser(string $email, string $cpf): User
    {
        return User::create([
            'name' => 'Cliente Mobile',
            'cpf' => $cpf,
            'email' => $email,
            'phone' => '(11) 99999-1234',
            'password' => 'senha-segura',
        ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'API '.uniqid(),
            'slug' => 'api-'.uniqid(),
            'display_order' => 1,
        ]);
    }

    private function createProduct(
        Category $category,
        string $name,
        bool $active,
        string $price = '10.00'
    ): Product {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Produto da API.',
            'ingredients' => 'Ingredientes',
            'price' => $price,
            'active' => $active,
        ]);
    }

    private function createOrder(User $user): Order
    {
        return Order::create([
            'order_number' => 'FF'.strtoupper(substr(uniqid(), -10)),
            'user_id' => $user->id,
            'status' => 'novo',
            'delivery_method' => 'retirada',
            'delivery_address_snapshot' => null,
            'payment_method' => 'pix',
            'payment_status' => 'pago',
            'subtotal' => '10.00',
            'delivery_fee' => '0.00',
            'total' => '10.00',
        ]);
    }

    private function registrationPayload(): array
    {
        return array_merge($this->addressPayload(), [
            'name' => 'Maria Mobile',
            'cpf' => '529.982.247-25',
            'email' => 'mobile@example.com',
            'phone' => '(11) 99999-1234',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
            'privacy_accepted' => true,
        ]);
    }

    private function addressPayload(string $street = 'Rua do Cliente'): array
    {
        return [
            'street' => $street,
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'complement' => 'Casa',
            'is_default' => true,
        ];
    }
}
