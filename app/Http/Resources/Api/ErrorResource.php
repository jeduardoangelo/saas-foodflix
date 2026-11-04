<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'error' => [
                'code' => $this->resource['code'],
                'message' => $this->resource['message'],
                'details' => $this->resource['details'] ?? null,
            ],
        ];
    }
}
