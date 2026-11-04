<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\User;
use App\Models\StoreSetting;
use App\Services\Cart\CartService;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    private $cart;

    private $gateway;

    public function __construct(CartService $cart, PaymentGatewayInterface $gateway)
    {
        $this->cart = $cart;
        $this->gateway = $gateway;
    }

    public function preview(User $user): array
    {
        $cart = $this->requireCart();
        $settings = StoreSetting::current();
        $deliveryFeeCents = $this->moneyToCents($settings->delivery_fee);

        return [
            'cart' => $cart,
            'defaultAddress' => $user->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first(),
            'storeSettings' => $settings,
            'deliveryFeeCents' => $deliveryFeeCents,
            'deliveryTotalCents' => $cart['subtotal_cents'] + $deliveryFeeCents,
        ];
    }

    public function placeOrder(User $user, array $data): Order
    {
        $cart = $this->requireCart();
        $order = $this->createOrder($user, $data, $cart);
        $this->cart->clear();

        return $order;
    }

    public function placeOrderFromItems(User $user, array $data): Order
    {
        $cart = $this->cart->summaryForItems($data['items']);

        if ($cart['is_empty']) {
            throw new EmptyCartException('Seu carrinho está vazio. Adicione produtos antes de finalizar.');
        }

        return $this->createOrder($user, $data, $cart);
    }

    private function createOrder(User $user, array $data, array $cart): Order
    {
        $settings = StoreSetting::current();
        $isDelivery = $data['delivery_method'] === 'entrega';
        $deliveryFeeCents = $isDelivery ? $this->moneyToCents($settings->delivery_fee) : 0;
        $totalCents = $cart['subtotal_cents'] + $deliveryFeeCents;

        $order = new Order([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $user->id,
            'status' => 'novo',
            'delivery_method' => $data['delivery_method'],
            'delivery_address_snapshot' => $isDelivery ? $this->addressSnapshot($data) : null,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pendente',
            'subtotal' => $this->centsToMoney($cart['subtotal_cents']),
            'delivery_fee' => $this->centsToMoney($deliveryFeeCents),
            'total' => $this->centsToMoney($totalCents),
        ]);

        $paymentResult = $this->gateway->charge($order);

        if (! $paymentResult->successful()) {
            throw new PaymentFailedException(
                $paymentResult->message() ?: 'Não foi possível aprovar o pagamento. Tente novamente.'
            );
        }

        $order->payment_status = 'pago';

        $order = DB::transaction(function () use ($order, $cart, $paymentResult) {
            $order->save();

            foreach ($cart['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name_snapshot' => $item['product']->name,
                    'unit_price_snapshot' => $this->centsToMoney($item['unit_price_cents']),
                    'quantity' => $item['quantity'],
                    'subtotal' => $this->centsToMoney($item['subtotal_cents']),
                ]);
            }

            $order->payment()->create([
                'gateway' => $paymentResult->gateway(),
                'gateway_transaction_id' => $paymentResult->transactionId(),
                'method' => $this->paymentMethodForGateway($order->payment_method),
                'status' => 'aprovado',
                'amount' => $order->total,
                'paid_at' => now(),
            ]);

            return $order;
        });

        return $order->load(['items', 'payment']);
    }

    private function requireCart(): array
    {
        $cart = $this->cart->summary();

        if ($cart['is_empty']) {
            throw new EmptyCartException('Seu carrinho está vazio. Adicione produtos antes de finalizar.');
        }

        return $cart;
    }

    private function addressSnapshot(array $data): array
    {
        return [
            'street' => $data['street'],
            'number' => $data['number'],
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'],
            'complement' => $data['complement'] ?? null,
        ];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'FF'.now()->format('ymd').strtoupper(Str::random(8));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function paymentMethodForGateway(string $method): string
    {
        return $method === 'cartao_credito' ? 'credit_card' : $method;
    }

    private function moneyToCents($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function centsToMoney(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
