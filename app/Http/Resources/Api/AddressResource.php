<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'street' => $this->street,
            'number' => $this->number,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'complement' => $this->complement,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
