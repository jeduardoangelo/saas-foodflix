<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    private $products;

    public function __construct(ProductService $products)
    {
        $this->products = $products;
    }

    public function index(): View
    {
        return view('admin.products.index', [
            'products' => $this->products->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => $this->products->categories(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->products->categories(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product = $this->products->toggleActive($product);
        $status = $product->active ? 'ativado' : 'desativado';

        return back()->with('success', "Produto {$status} com sucesso.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produto excluído com sucesso.');
    }
}
