<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StoreSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth:admin')->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [DashboardController::class, 'index'])->name('orders.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/orders.xlsx', [ReportController::class, 'exportOrders'])->name('reports.orders.export');
    Route::get('/reports/customers.xlsx', [ReportController::class, 'exportCustomers'])->name('reports.customers.export');
    Route::patch('/orders/{order}/advance', [OrderStatusController::class, 'advance'])
        ->where('order', '[0-9]+')
        ->name('orders.advance');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])
        ->name('products.toggle');
    Route::resource('/products', ProductController::class)->except('show');
    Route::get('/settings', [StoreSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
