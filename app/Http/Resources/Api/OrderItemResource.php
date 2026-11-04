<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->product_name_snapshot,
            'unit_price' => $this->unit_price_snapshot,
            'quantity' => (int) $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
