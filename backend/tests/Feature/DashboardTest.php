<?php

/*
|--------------------------------------------------------------------------
| Testes do dashboard e metricas.
|
| Valida que os KPIs (receita, vendas, estoque baixo, ticket medio)
| sao calculados corretamente com base nos dados reais, e que
| os endpoints de graficos e feed retornam a estrutura esperada.
|--------------------------------------------------------------------------
*/

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->seller = User::factory()->create(['role' => 'seller']);
    $this->category = Category::factory()->create();
});

// ==========================================
// KPIs do summary
// ==========================================

test('summary retorna todos os KPIs esperados', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary')
        ->assertOk()
        ->assertJsonStructure([
            'active_products',
            'monthly_revenue',
            'previous_revenue',
            'revenue_change',
            'low_stock_count',
            'zero_stock_count',
            'monthly_sales_count',
            'previous_sales_count',
            'sales_change',
            'average_ticket',
            'total_stock_value',
        ]);
});

test('summary calcula receita mensal corretamente', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 100,
        'sale_price' => 50.00,
        'cost_price' => 20.00,
        'is_active' => true,
    ]);

    // Cria 2 vendas neste mes
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]], // 100.00
        ])->assertCreated();

    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]], // 150.00
        ])->assertCreated();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary');

    $response->assertOk();
    expect((float) $response->json('monthly_revenue'))->toBe(250.0);
    expect($response->json('monthly_sales_count'))->toBe(2);
    expect((float) $response->json('average_ticket'))->toBe(125.0);
});

test('summary conta produtos ativos corretamente', function () {
    Product::factory()->count(5)->create([
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);
    Product::factory()->count(2)->create([
        'category_id' => $this->category->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary');

    expect($response->json('active_products'))->toBe(5);
});

test('summary separa estoque zerado de estoque baixo', function () {
    // Estoque zerado (stock = 0)
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 0,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    // Estoque baixo (stock > 0, stock <= min_stock)
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 3,
        'min_stock' => 10,
        'is_active' => true,
    ]);

    // Estoque normal (stock > min_stock)
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary');

    expect($response->json('zero_stock_count'))->toBe(1);
    expect($response->json('low_stock_count'))->toBe(1);
});

test('summary calcula valor total em estoque com preco de custo', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 10,
        'cost_price' => 25.00,
        'sale_price' => 50.00,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 5,
        'cost_price' => 100.00,
        'sale_price' => 200.00,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary');

    // (10 x 25) + (5 x 100) = 250 + 500 = 750
    expect((float) $response->json('total_stock_value'))->toBe(750.0);
});

test('summary retorna zeros quando nao ha vendas', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/summary');

    expect((float) $response->json('monthly_revenue'))->toBe(0.0);
    expect($response->json('monthly_sales_count'))->toBe(0);
    expect((float) $response->json('average_ticket'))->toBe(0.0);
});

// ==========================================
// Graficos
// ==========================================

test('revenue chart retorna dados com label e value', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/dashboard/revenue-chart')
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['label', 'value'],
        ]);
});

test('revenue chart aceita periodo 7d', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/revenue-chart?period=7d')
        ->assertOk();

    // 7 dias retorna exatamente 7 pontos
    expect(count($response->json()))->toBe(7);
});

test('revenue chart aceita periodo 12m', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/revenue-chart?period=12m')
        ->assertOk();

    // 12 meses retorna exatamente 12 pontos
    expect(count($response->json()))->toBe(12);
});

test('revenue chart padrao e 6 meses', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/revenue-chart')
        ->assertOk();

    expect(count($response->json()))->toBe(6);
});

test('top products retorna dados formatados', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/dashboard/top-products')
        ->assertOk();
});

test('revenue by category retorna dados formatados', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/dashboard/revenue-by-category')
        ->assertOk();
});

// ==========================================
// Activity feed
// ==========================================

test('activity feed retorna atividades recentes', function () {
    // Cria uma venda para gerar activity log
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 10,
        'sale_price' => 30.00,
        'cost_price' => 15.00,
        'is_active' => true,
    ]);

    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/activity-feed');

    $response->assertOk();
    $data = $response->json();
    expect(count($data))->toBeGreaterThanOrEqual(1);
    expect($data[0])->toHaveKeys(['id', 'action', 'entity', 'created_at']);
});

test('activity feed retorna vazio quando nao ha atividade', function () {
    $response = $this->actingAs($this->admin)
        ->getJson('/api/dashboard/activity-feed');

    $response->assertOk();
    expect(count($response->json()))->toBe(0);
});
