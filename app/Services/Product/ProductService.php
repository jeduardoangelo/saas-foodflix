<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function paginate(): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->latest('id')
            ->paginate(10);
    }

    public function categories(): Collection
    {
        return Category::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function create(array $attributes): Product
    {
        $attributes = $this->storeImage($attributes);

        return Product::create($attributes);
    }

    public function update(Product $product, array $attributes): Product
    {
        $previousImage = $product->image_url;
        $attributes = $this->storeImage($attributes);

        $product->update($attributes);

        if (isset($attributes['image_url']) && $previousImage) {
            Storage::disk('public')->delete($previousImage);
        }

        return $product->refresh();
    }

    public function toggleActive(Product $product): Product
    {
        $product->update(['active' => ! $product->active]);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function storeImage(array $attributes): array
    {
        $image = $attributes['image'] ?? null;
        unset($attributes['image']);

        if ($image instanceof UploadedFile) {
            $attributes['image_url'] = $image->store('products', 'public');
        }

        return $attributes;
    }
}
