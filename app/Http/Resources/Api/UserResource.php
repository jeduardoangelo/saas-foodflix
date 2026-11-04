<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'phone' => $this->phone,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
