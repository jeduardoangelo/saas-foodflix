<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Catalog\CatalogService;
use App\Services\Order\CustomerOrderService;
use Illuminate\View\View;

class OrderController extends Controller
{
    private $catalog;
    private $orders;

    public function __construct(CatalogService $catalog, CustomerOrderService $orders)
    {
        $this->catalog = $catalog;
        $this->orders = $orders;
    }

    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('site.orders.index', [
            'orders' => $this->orders->forUser(auth('web')->user()),
            'navigationCategories' => $this->catalog->navigationCategories(),
        ]);
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
