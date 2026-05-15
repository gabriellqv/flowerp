<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Stock\CategoryController;
use App\Http\Controllers\Stock\ProductController;
use Illuminate\Support\Facades\Route;

// Publicas (sem token)
Route::post('/auth/login', [AuthController::class, 'login']);

// Protegidas (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'me');
        Route::put('profile', 'updateProfile');
        Route::put('password', 'updatePassword');
    });

    // Dashboard
    Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        Route::get('summary', 'summary');
        Route::get('revenue-chart', 'revenueChart');
        Route::get('activity-feed', 'activityFeed');
        Route::get('activity-log', 'activityLog');
        Route::get('top-products', 'topProducts');
        Route::get('revenue-by-category', 'revenueByCategory');
    });

    // Produtos
    Route::prefix('products')->controller(ProductController::class)->group(function () {
        Route::get('low-stock', 'lowStock');
        Route::post('bulk-delete', 'bulkDelete')->middleware('role:admin,manager');
        Route::patch('{product}/toggle-active', 'toggleActive')->middleware('role:admin,manager');

        Route::get('', 'index');
        Route::post('', 'store')->middleware('role:admin,manager');
        Route::get('{product}', 'show');
        Route::put('{product}', 'update')->middleware('role:admin,manager');
        Route::delete('{product}', 'destroy')->middleware('role:admin');
    });

    // Categorias
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store')->middleware('role:admin,manager');
        Route::get('{category}', 'show');
        Route::put('{category}', 'update')->middleware('role:admin,manager');
        Route::delete('{category}', 'destroy')->middleware('role:admin,manager');
    });

    // Clientes
    Route::prefix('customers')->controller(CustomerController::class)->group(function () {
        Route::post('bulk-delete', 'bulkDelete')->middleware('role:admin,manager');
        Route::patch('{customer}/toggle-active', 'toggleActive')->middleware('role:admin,manager');

        Route::get('', 'index');
        Route::post('', 'store')->middleware('role:admin,manager');
        Route::get('{customer}', 'show');
        Route::put('{customer}', 'update')->middleware('role:admin,manager');
    });

    // Vendas
    Route::prefix('sales')->controller(SaleController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store')->middleware('role:admin,manager,seller');
        Route::get('{sale}', 'show');
    });
});
