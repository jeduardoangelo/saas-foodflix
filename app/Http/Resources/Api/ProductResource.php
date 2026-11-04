<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
