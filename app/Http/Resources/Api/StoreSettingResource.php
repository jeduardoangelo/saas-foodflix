<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'store_name' => $this->store_name,
            'cnpj' => $this->cnpj,
            'physical_address' => $this->physical_address,
            'opening_hours' => $this->opening_hours,
            'delivery_fee' => $this->delivery_fee,
            'estimated_delivery_minutes' => (int) $this->estimated_delivery_minutes,
        ];
    }
}
