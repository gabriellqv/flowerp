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
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/dashboard/activity-feed', [DashboardController::class, 'activityFeed']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/revenue-by-category', [DashboardController::class, 'revenueByCategory']);

    // Produtos
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])
        ->middleware('role:admin,manager');
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('role:admin,manager');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('role:admin,manager');
    Route::patch('/products/{product}/toggle-active', [ProductController::class, 'toggleActive'])
        ->middleware('role:admin,manager');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('role:admin');

    // Categorias
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('role:admin,manager');
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('role:admin,manager');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('role:admin,manager');

    // Clientes
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers/bulk-delete', [CustomerController::class, 'bulkDelete'])
        ->middleware('role:admin,manager');
    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('role:admin,manager');
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('role:admin,manager');
    Route::patch('/customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])
        ->middleware('role:admin,manager');

    // Vendas
    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware('role:admin,manager,seller');
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
});
