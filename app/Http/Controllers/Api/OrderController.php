<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\Api\ErrorResource;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\Order\CheckoutService;
use App\Services\Order\CustomerOrderService;
use App\Services\Order\EmptyCartException;
use App\Services\Order\PaymentFailedException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $checkout;
    private $orders;

    public function __construct(CheckoutService $checkout, CustomerOrderService $orders)
    {
        $this->checkout = $checkout;
        $this->orders = $orders;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        return OrderResource::collection($this->orders->forUser($request->user('api')));
    }

    public function store(CheckoutRequest $request)
    {
        try {
            $order = $this->checkout->placeOrderFromItems(
                $request->user('api'),
                $request->validated()
            );
        } catch (EmptyCartException $exception) {
            return $this->error('empty_cart', $exception->getMessage(), 422);
        } catch (PaymentFailedException $exception) {
            return $this->error('payment_failed', $exception->getMessage(), 422);
        }

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return new OrderResource($order->load(['items', 'payment']));
    }

    private function error(string $code, string $message, int $status)
    {
        return (new ErrorResource(compact('code', 'message')))
            ->response()
            ->setStatusCode($status);
    }
}
