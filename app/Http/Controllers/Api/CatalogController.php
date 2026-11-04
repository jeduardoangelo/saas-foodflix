<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\ProductResource;
use App\Services\Catalog\CatalogService;

class CatalogController extends Controller
{
    private $catalog;

    public function __construct(CatalogService $catalog)
    {
        $this->catalog = $catalog;
    }

    public function categories()
    {
        return CategoryResource::collection($this->catalog->categories());
    }

    public function products()
    {
        return ProductResource::collection($this->catalog->products());
    }

    public function product(int $product)
    {
        return new ProductResource($this->catalog->product($product));
    }
}
