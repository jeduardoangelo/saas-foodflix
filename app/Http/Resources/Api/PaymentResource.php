<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'gateway' => $this->gateway,
            'transaction_id' => $this->gateway_transaction_id,
            'method' => $this->method,
            'status' => $this->status,
            'amount' => $this->amount,
            'paid_at' => optional($this->paid_at)->toIso8601String(),
        ];
    }
}
