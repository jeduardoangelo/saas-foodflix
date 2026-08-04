<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CatalogService
{
    public function home(?string $search = null): array
    {
        $search = $search !== null && $search !== '' ? Str::lower($search) : null;

        $productConstraint = function ($query) use ($search) {
            $query->active()->orderBy('name');

            if ($search !== null) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.$search.'%']);
            }
        };

        $categories = Category::query()
            ->whereHas('products', $productConstraint)
            ->with(['products' => $productConstraint])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return [
            'categories' => $categories,
            'featuredProducts' => $search === null ? $this->featuredProducts() : new Collection(),
            'navigationCategories' => $this->navigationCategories(),
            'search' => $search,
        ];
    }

    public function product(int $id): Product
    {
        return Product::query()
            ->active()
            ->with('category')
            ->findOrFail($id);
    }

    public function navigationCategories(): Collection
    {
        return Category::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    private function featuredProducts(): Collection
    {
        $ids = collect(config('catalog.featured_product_ids', []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return new Collection();
        }

        $positions = $ids->flip();

        return Product::query()
            ->active()
            ->with('category')
            ->whereIn('id', $ids->all())
            ->get()
            ->sortBy(function (Product $product) use ($positions) {
                return $positions->get($product->id);
            })
            ->values();
    }
}
