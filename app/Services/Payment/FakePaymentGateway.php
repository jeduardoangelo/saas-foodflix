<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayInterface
{
    private $approveCharges;

    public function __construct(bool $approveCharges = true)
    {
        $this->approveCharges = $approveCharges;
    }

    public function charge(Order $order): PaymentResult
    {
        $transactionId = 'fake_'.Str::uuid();

        if (! $this->approveCharges) {
            return PaymentResult::declined(
                'fake',
                $transactionId,
                'O pagamento foi recusado. Revise a forma de pagamento e tente novamente.'
            );
        }

        return PaymentResult::approved('fake', $transactionId);
    }
}
