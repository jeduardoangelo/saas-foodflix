<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\AddCartItemRequest;
use App\Http\Requests\Site\UpdateCartItemRequest;
use App\Services\Cart\CartService;
use App\Services\Catalog\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    private $cart;

    private $catalog;

    public function __construct(CartService $cart, CatalogService $catalog)
    {
        $this->cart = $cart;
        $this->catalog = $catalog;
    }

    public function index(): View
    {
        return view('site.cart.index', [
            'cart' => $this->cart->summary(),
            'navigationCategories' => $this->catalog->navigationCategories(),
        ]);
    }

    public function store(AddCartItemRequest $request, int $product): RedirectResponse
    {
        $this->cart->add($product, (int) $request->validated()['quantity']);

        return back()
            ->with('cart_open', true)
            ->with('cart_message', 'Produto adicionado ao carrinho.');
    }

    public function update(UpdateCartItemRequest $request, int $product): RedirectResponse
    {
        $quantity = (int) $request->validated()['quantity'];
        $this->cart->update($product, $quantity);

        return back()
            ->with('cart_open', $request->boolean('mini_cart'))
            ->with('cart_message', $quantity === 0 ? 'Produto removido do carrinho.' : 'Quantidade atualizada.');
    }

    public function destroy(int $product): RedirectResponse
    {
        $this->cart->remove($product);

        return back()
            ->with('cart_open', request()->boolean('mini_cart'))
            ->with('cart_message', 'Produto removido do carrinho.');
    }
}
