<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'access_token' => $this->resource['token'],
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => new UserResource($this->resource['user']),
        ];
    }
}
