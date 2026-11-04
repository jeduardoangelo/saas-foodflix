<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogService
{
    public function categories(): Collection
    {
        return Category::query()
            ->whereHas('products', function ($query) {
                $query->active();
            })
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->active()
            ->with('category')
            ->orderBy('name')
            ->paginate(15);
    }

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
        $popularity = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'pago')
            ->select('order_items.product_id')
            ->selectRaw('COUNT(DISTINCT orders.id) as order_count')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy('order_items.product_id');

        return Product::query()
            ->active()
            ->with('category')
            ->joinSub($popularity, 'product_popularity', function ($join) {
                $join->on('products.id', '=', 'product_popularity.product_id');
            })
            ->select('products.*')
            ->orderByDesc('product_popularity.order_count')
            ->orderByDesc('product_popularity.units_sold')
            ->limit((int) config('catalog.featured_products_limit', 4))
            ->get();
    }
}
