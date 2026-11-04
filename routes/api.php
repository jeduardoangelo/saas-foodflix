<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StoreSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->as('api.v1.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:api-login')
        ->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

    Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
    Route::get('/products', [CatalogController::class, 'products'])->name('products.index');
    Route::get('/products/{product}', [CatalogController::class, 'product'])
        ->where('product', '[0-9]+')
        ->name('products.show');
    Route::get('/store-settings', [StoreSettingController::class, 'show'])->name('store-settings.show');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::get('/addresses/{address}', [AddressController::class, 'show'])->name('addresses.show');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});
