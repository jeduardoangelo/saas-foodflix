<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'delivery_method' => $this->delivery_method,
            'delivery_address' => $this->delivery_address_snapshot,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
