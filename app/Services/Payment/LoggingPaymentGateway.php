<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class LoggingPaymentGateway implements PaymentGatewayInterface
{
    private $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function charge(Order $order): PaymentResult
    {
        $result = $this->gateway->charge($order);

        if (! $result->successful()) {
            Log::channel('payments')->error('Falha na cobrança do pedido.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'gateway' => $result->gateway(),
                'message' => $result->message(),
            ]);
        }

        return $result;
    }
}
