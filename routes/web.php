<?php

use App\Http\Controllers\Site\CatalogController;
use App\Http\Controllers\Site\Auth\LoginController;
use App\Http\Controllers\Site\Auth\RegisterController;
use App\Http\Controllers\Site\CartController;
use App\Http\Controllers\Site\CheckoutController;
use App\Http\Controllers\Site\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:web')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('site.login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:client-login')
        ->name('site.login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('site.register');
    Route::post('/register', [RegisterController::class, 'store'])->name('site.register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth:web')
    ->name('site.logout');

Route::get('/', [CatalogController::class, 'index'])->name('site.home');
Route::view('/privacidade', 'site.privacy')->name('site.privacy');
Route::get('/cart', [CartController::class, 'index'])->name('site.cart.index');
Route::post('/cart/items/{product}', [CartController::class, 'store'])
    ->where('product', '[0-9]+')
    ->name('site.cart.items.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])
    ->where('product', '[0-9]+')
    ->name('site.cart.items.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])
    ->where('product', '[0-9]+')
    ->name('site.cart.items.destroy');

Route::middleware('auth:web')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('site.checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('site.checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])
        ->where('order', '[0-9]+')
        ->name('site.checkout.success');
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->where('order', '[0-9]+')
        ->name('site.orders.show');
});

Route::get('/products/{product}', [CatalogController::class, 'show'])
    ->where('product', '[0-9]+')
    ->name('site.products.show');
