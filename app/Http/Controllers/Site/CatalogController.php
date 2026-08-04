<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CatalogSearchRequest;
use App\Services\Catalog\CatalogService;
use Illuminate\View\View;

class CatalogController extends Controller
{
    private $catalog;

    public function __construct(CatalogService $catalog)
    {
        $this->catalog = $catalog;
    }

    public function index(CatalogSearchRequest $request): View
    {
        $validated = $request->validated();

        return view('site.catalog.index', $this->catalog->home($validated['q'] ?? null));
    }

    public function show(int $product): View
    {
        return view('site.catalog.show', [
            'product' => $this->catalog->product($product),
            'navigationCategories' => $this->catalog->navigationCategories(),
        ]);
    }
}
