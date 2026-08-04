<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function charge(Order $order): PaymentResult;
}
