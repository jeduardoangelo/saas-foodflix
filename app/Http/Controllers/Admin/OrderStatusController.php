<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderDashboardService;
use DomainException;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    private $orders;

    public function __construct(OrderDashboardService $orders)
    {
        $this->orders = $orders;
    }

    public function advance(Request $request, int $order)
    {
        try {
            $updatedOrder = $this->orders->advance($order);
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['order' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            $nextStatus = $this->orders->nextStatus($updatedOrder->status);

            return response()->json([
                'order_id' => $updatedOrder->id,
                'status' => $updatedOrder->status,
                'status_label' => $this->orders->statusLabel($updatedOrder->status),
                'next_status' => $nextStatus,
                'next_label' => $nextStatus ? $this->orders->statusLabel($nextStatus) : null,
                'metrics' => $this->orders->metrics(),
            ]);
        }

        return back()->with('success', 'Pedido avançado para '.$this->orders->statusLabel($updatedOrder->status).'.');
    }
}
