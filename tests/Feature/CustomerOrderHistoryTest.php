<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_history_requires_customer_authentication(): void
    {
        $this->get('/orders')->assertRedirect('/login');
    }

    public function test_customer_sees_only_own_orders_newest_first_and_detail_links(): void
    {
        $user = $this->createUser('history@example.com', '529.982.247-25');
        $other = $this->createUser('other-history@example.com', '111.444.777-35');
        $older = $this->createOrder($user, 'FFOLDER', now()->subDay());
        $newer = $this->createOrder($user, 'FFNEWER', now());
        $this->createOrder($other, 'FFOTHER', now()->addMinute());

        $response = $this->actingAs($user, 'web')->get('/orders');

        $response->assertOk()
            ->assertSee('Meus Pedidos')
            ->assertSeeInOrder(['FFNEWER', 'FFOLDER'])
            ->assertSee(route('site.orders.show', $newer))
            ->assertSee(route('site.orders.show', $older))
            ->assertDontSee('FFOTHER');
    }

    public function test_order_history_is_paginated_ten_per_page(): void
    {
        $user = $this->createUser('pagination@example.com', '529.982.247-25');

        for ($index = 1; $index <= 12; $index++) {
            $this->createOrder(
                $user,
                'FFPAGE'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                now()->subMinutes($index)
            );
        }

        $firstPage = $this->actingAs($user, 'web')->get('/orders');
        $firstPage->assertOk()->assertSee('Página 1 de 2')->assertSee('FFPAGE01')->assertSee('FFPAGE10')->assertDontSee('FFPAGE11');

        $secondPage = $this->actingAs($user, 'web')->get('/orders?page=2');
        $secondPage->assertOk()->assertSee('Página 2 de 2')->assertSee('FFPAGE11')->assertSee('FFPAGE12')->assertDontSee('FFPAGE10');
    }

    public function test_customer_without_orders_sees_empty_state_and_navigation_link(): void
    {
        $user = $this->createUser('empty-history@example.com', '529.982.247-25');

        $this->actingAs($user, 'web')->get('/orders')
            ->assertOk()
            ->assertSee('Você ainda não fez nenhum pedido');

        $this->actingAs($user, 'web')->get('/')
            ->assertOk()
            ->assertSee('Meus Pedidos')
            ->assertSee(route('site.orders.index'));
    }

    private function createUser(string $email, string $cpf): User
    {
        return User::create([
            'name' => 'Cliente Histórico',
            'cpf' => $cpf,
            'email' => $email,
            'phone' => '(11) 99999-1234',
            'password' => 'senha-segura',
        ]);
    }

    private function createOrder(User $user, string $number, $createdAt): Order
    {
        $order = new Order();
        $order->forceFill([
            'order_number' => $number,
            'user_id' => $user->id,
            'status' => 'novo',
            'delivery_method' => 'retirada',
            'delivery_address_snapshot' => null,
            'payment_method' => 'pix',
            'payment_status' => 'pago',
            'subtotal' => '25.00',
            'delivery_fee' => '0.00',
            'total' => '25.00',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }
}
