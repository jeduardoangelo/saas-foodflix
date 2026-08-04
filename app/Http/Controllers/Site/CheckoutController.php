<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CheckoutRequest;
use App\Services\Catalog\CatalogService;
use App\Services\Order\CheckoutService;
use App\Services\Order\EmptyCartException;
use App\Services\Order\PaymentFailedException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private $checkout;

    private $catalog;

    public function __construct(CheckoutService $checkout, CatalogService $catalog)
    {
        $this->checkout = $checkout;
        $this->catalog = $catalog;
    }

    public function create(Request $request)
    {
        try {
            $data = $this->checkout->preview($request->user('web'));
        } catch (EmptyCartException $exception) {
            return redirect()->route('site.cart.index')->with('cart_message', $exception->getMessage());
        }

        $data['navigationCategories'] = $this->catalog->navigationCategories();

        return view('site.checkout.create', $data);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $order = $this->checkout->placeOrder($request->user('web'), $request->validated());
        } catch (EmptyCartException $exception) {
            return redirect()->route('site.cart.index')->with('cart_message', $exception->getMessage());
        } catch (PaymentFailedException $exception) {
            return back()
                ->withErrors(['payment_method' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('site.checkout.success', $order);
    }

    public function success(Request $request, int $order): View
    {
        return view('site.checkout.success', [
            'order' => $this->checkout->findForUser($order, $request->user('web')),
            'navigationCategories' => $this->catalog->navigationCategories(),
        ]);
    }
}
