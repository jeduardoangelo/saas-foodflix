<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return ['message' => $this->resource['message']];
    }
}
