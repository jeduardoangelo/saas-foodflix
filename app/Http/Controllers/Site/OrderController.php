<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Catalog\CatalogService;
use Illuminate\View\View;

class OrderController extends Controller
{
    private $catalog;

    public function __construct(CatalogService $catalog)
    {
        $this->catalog = $catalog;
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        return view('site.orders.show', [
            'order' => $order->load(['items', 'payment']),
            'navigationCategories' => $this->catalog->navigationCategories(),
        ]);
    }
}
