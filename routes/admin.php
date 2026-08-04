<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StoreSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
});

Route::middleware('auth:admin')->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])
        ->name('products.toggle');
    Route::resource('/products', ProductController::class)->except('show');
    Route::get('/settings', [StoreSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
