<?php

namespace App\Services\Cart;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart.items';

    private const MAX_QUANTITY = 99;

    public function add(int $productId, int $quantity): array
    {
        $product = Product::query()->active()->findOrFail($productId);
        $items = $this->storedItems();
        $currentQuantity = $items[$product->id] ?? 0;

        $items[$product->id] = min(self::MAX_QUANTITY, $currentQuantity + $quantity);
        $this->store($items);

        return $this->summary();
    }

    public function update(int $productId, int $quantity): array
    {
        $items = $this->storedItems();

        if ($quantity <= 0) {
            unset($items[$productId]);
        } elseif (array_key_exists($productId, $items)) {
            $items[$productId] = min(self::MAX_QUANTITY, $quantity);
        }

        $this->store($items);

        return $this->summary();
    }

    public function remove(int $productId): array
    {
        $items = $this->storedItems();
        unset($items[$productId]);
        $this->store($items);

        return $this->summary();
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function summary(): array
    {
        $storedItems = $this->storedItems();

        if ($storedItems === []) {
            return $this->emptySummary();
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', array_keys($storedItems))
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotalCents = 0;
        $validStoredItems = [];

        foreach ($storedItems as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $unitPriceCents = (int) round(((float) $product->price) * 100);
            $itemSubtotalCents = $unitPriceCents * $quantity;
            $subtotalCents += $itemSubtotalCents;
            $validStoredItems[$product->id] = $quantity;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price_cents' => $unitPriceCents,
                'subtotal_cents' => $itemSubtotalCents,
            ];
        }

        if ($validStoredItems !== $storedItems) {
            $this->store($validStoredItems);
        }

        return [
            'items' => $items,
            'item_count' => array_sum($validStoredItems),
            'subtotal_cents' => $subtotalCents,
            'total_cents' => $subtotalCents,
            'is_empty' => $items === [],
        ];
    }

    private function storedItems(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $validItems = [];

        foreach ($items as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($productId > 0 && $quantity > 0) {
                $validItems[$productId] = min(self::MAX_QUANTITY, $quantity);
            }
        }

        return $validItems;
    }

    private function store(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    private function emptySummary(): array
    {
        return [
            'items' => [],
            'item_count' => 0,
            'subtotal_cents' => 0,
            'total_cents' => 0,
            'is_empty' => true,
        ];
    }
}
