<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private $orders;

    public function __construct(OrderDashboardService $orders)
    {
        $this->orders = $orders;
    }

    public function index(): View
    {
        return view('admin.dashboard', $this->orders->dashboard());
    }
}
