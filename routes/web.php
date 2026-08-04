<?php

use App\Http\Controllers\Site\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('site.home');
Route::get('/products/{product}', [CatalogController::class, 'show'])
    ->where('product', '[0-9]+')
    ->name('site.products.show');
