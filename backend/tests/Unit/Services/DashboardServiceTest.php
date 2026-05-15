<?php

/*
|--------------------------------------------------------------------------
| Testes unitarios do DashboardService.
|
| Valida o calculo de KPIs com comparativo mensal, grafico
| de receita em diferentes periodos, feed de atividade,
| top produtos e receita por categoria.
|--------------------------------------------------------------------------
*/

use App\Enums\SaleStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->dashboardService = new DashboardService;
    $this->category = Category::factory()->create(['name' => 'Testes']);
    $this->seller = User::factory()->create(['role' => 'seller']);

    Cache::flush();
});

test('getSummary retorna KPIs do mes atual com valores zerados quando sem vendas', function () {
    Product::factory()->count(3)->create([
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);

    $summary = $this->dashboardService->getSummary();

    expect($summary['active_products'])->toBe(3);
    expect((float) $summary['monthly_revenue'])->toBe(0.0);
    expect($summary['monthly_sales_count'])->toBe(0);
    expect((float) $summary['average_ticket'])->toBe(0.0);
});

test('getSummary calcula receita do mes atual corretamente', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'sale_price' => 100.00,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $sale = Sale::factory()->create([
        'seller_id' => $this->seller->id,
        'total_amount' => 300.00,
        'status' => SaleStatus::COMPLETED,
        'created_at' => now(),
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 100.00,
    ]);

    Cache::flush();
    $summary = $this->dashboardService->getSummary();

    expect($summary['monthly_revenue'])->toBe(300.0);
    expect($summary['monthly_sales_count'])->toBe(1);
    expect($summary['average_ticket'])->toBe(300.0);
});

test('getSummary calcula estoque baixo e zerado separadamente', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 0,
        'min_stock' => 10,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 3,
        'min_stock' => 10,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 50,
        'min_stock' => 10,
        'is_active' => true,
    ]);

    $summary = $this->dashboardService->getSummary();

    expect($summary['zero_stock_count'])->toBe(1);
    expect($summary['low_stock_count'])->toBe(1);
});

test('getRevenueChart retorna 7 labels para periodo de 7 dias', function () {
    $chart = $this->dashboardService->getRevenueChart('7d');

    expect($chart)->toHaveCount(7);
    expect($chart[0])->toHaveKeys(['label', 'value']);
});

test('getRevenueChart retorna o padrao 6 meses para periodo invalido', function () {
    $chart = $this->dashboardService->getRevenueChart('qualquer');

    expect($chart)->toHaveCount(6);
});

test('getRecentActivity retorna atividades mais recentes', function () {
    ActivityLog::factory()->count(15)->create([
        'user_id' => $this->seller->id,
    ]);

    $activities = $this->dashboardService->getRecentActivity(5);

    expect($activities)->toHaveCount(5);
    expect($activities->first()->user_id)->toBe($this->seller->id);
});

test('getPaginatedActivity filtra por acao', function () {
    ActivityLog::factory()->create(['action' => 'PRODUCT_CREATED', 'user_id' => $this->seller->id]);
    ActivityLog::factory()->create(['action' => 'SALE_CREATED', 'user_id' => $this->seller->id]);

    $request = new Request(['action' => 'PRODUCT_CREATED']);
    $result = $this->dashboardService->getPaginatedActivity($request);

    expect($result->total())->toBe(1);
});

test('getPaginatedActivity busca por nome do usuario', function () {
    $user = User::factory()->create(['name' => 'Fulano de Tal', 'role' => 'seller']);
    ActivityLog::factory()->create(['user_id' => $user->id, 'action' => 'LOGIN']);
    ActivityLog::factory()->create(['user_id' => $this->seller->id, 'action' => 'LOGOUT']);

    $request = new Request(['search' => 'Fulano']);
    $result = $this->dashboardService->getPaginatedActivity($request);

    expect($result->total())->toBe(1);
});

test('getTopProducts retorna os produtos mais vendidos do mes', function () {
    $product1 = Product::factory()->create([
        'category_id' => $this->category->id,
        'sale_price' => 10.00,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);
    $product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'sale_price' => 20.00,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $sale = Sale::factory()->create([
        'seller_id' => $this->seller->id,
        'total_amount' => 100.00,
        'status' => SaleStatus::COMPLETED,
        'created_at' => now(),
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
        'quantity' => 10,
        'unit_price' => 10.00,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
        'quantity' => 3,
        'unit_price' => 20.00,
    ]);

    Cache::flush();
    $topProducts = $this->dashboardService->getTopProducts();

    expect($topProducts)->toHaveCount(2);
    expect($topProducts->first()->value)->toBe(10);
});

test('clearCache limpa todos os caches do dashboard', function () {
    Cache::put('dashboard.summary', ['teste' => true], now()->addHour());
    Cache::put('dashboard.revenue_chart.6m', ['teste' => true], now()->addHour());

    $this->dashboardService->clearCache();

    expect(Cache::has('dashboard.summary'))->toBeFalse();
    expect(Cache::has('dashboard.revenue_chart.6m'))->toBeFalse();
});
