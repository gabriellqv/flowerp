<?php

/*
|--------------------------------------------------------------------------
| Testes de vendas (regras de negocio criticas).
|
| Valida o fluxo transacional ACID: criacao de venda,
| baixa de estoque, registro de movimentacao, lancamento
| financeiro e log de atividade. Tambem testa cenarios
| de erro (estoque insuficiente, produto inativo, itens vazios).
|--------------------------------------------------------------------------
*/

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->seller = User::factory()->create(['role' => 'seller']);
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 10,
        'sale_price' => 25.00,
        'cost_price' => 10.00,
        'is_active' => true,
    ]);
});

// ==========================================
// Cenarios de sucesso
// ==========================================

test('cria venda com sucesso e retorna dados corretos', function () {
    $response = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 3,
            ]],
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'id', 'total_amount', 'status',
            'items' => [['product_id', 'quantity', 'unit_price']],
        ])
        ->assertJsonPath('status', 'COMPLETED');

    // total_amount retorna como string decimal do cast
    expect((float) $response->json('total_amount'))->toBe(75.0);
});

test('venda da baixa correta no estoque', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 4,
            ]],
        ])
        ->assertCreated();

    // Estoque deve diminuir de 10 para 6
    expect($this->product->fresh()->stock_quantity)->toBe(6);
});

test('venda registra movimentacao de saida no estoque', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]],
        ])
        ->assertCreated();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product->id,
        'type' => 'OUT',
        'quantity' => 2,
        'reason' => 'SALE',
    ]);
});

test('venda cria lancamento financeiro com valor correto', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]],
        ])
        ->assertCreated();

    $this->assertDatabaseHas('financial_entries', [
        'type' => 'INCOME',
        'amount' => 50.00,
        'category' => 'SALE',
        'is_paid' => true,
    ]);
});

test('venda gera log de atividade com dados do vendedor', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]],
        ])
        ->assertCreated();

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->seller->id,
        'action' => 'SALE_CREATED',
        'entity' => 'Sale',
    ]);
});

test('venda com multiplos itens calcula total corretamente', function () {
    $product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 5,
        'sale_price' => 10.00,
        'cost_price' => 5.00,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],  // 2 x 25 = 50
                ['product_id' => $product2->id, 'quantity' => 3],       // 3 x 10 = 30
            ],
        ]);

    $response->assertCreated();
    expect((float) $response->json('total_amount'))->toBe(80.0);

    // Ambos os estoques devem ser decrementados
    expect($this->product->fresh()->stock_quantity)->toBe(8);
    expect($product2->fresh()->stock_quantity)->toBe(2);
});

test('venda pode ser associada a um cliente', function () {
    $customer = Customer::factory()->create();

    $response = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]],
        ]);

    $response->assertCreated()
        ->assertJsonPath('customer.id', $customer->id);
});

test('venda funciona sem cliente (venda anonima)', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]],
        ])
        ->assertCreated()
        ->assertJsonPath('customer', null);
});

// ==========================================
// Cenarios de erro
// ==========================================

test('rejeita venda quando estoque e insuficiente', function () {
    $response = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 15, // So tem 10 em estoque
            ]],
        ])
        ->assertStatus(422);

    expect($response->json('message'))->toContain('Estoque insuficiente');

    // Estoque nao deve ter sido alterado
    expect($this->product->fresh()->stock_quantity)->toBe(10);
});

test('rejeita venda de produto inativo', function () {
    $this->product->update(['is_active' => false]);

    $response = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]],
        ])
        ->assertStatus(422);

    expect($response->json('message'))->toContain('inativo');
});

test('rejeita venda sem itens', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', ['items' => []])
        ->assertStatus(422);
});

test('rejeita venda com product_id inexistente', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => '00000000-0000-0000-0000-000000000000',
                'quantity' => 1,
            ]],
        ])
        ->assertStatus(422);
});

test('rejeita venda com quantidade zero', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 0,
            ]],
        ])
        ->assertStatus(422);
});

test('rejeita venda com quantidade negativa', function () {
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => -5,
            ]],
        ])
        ->assertStatus(422);
});

// ==========================================
// Consulta de vendas
// ==========================================

test('listagem de vendas retorna dados paginados', function () {
    // Cria uma venda para garantir que haja dados
    $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]],
        ])->assertCreated();

    $this->actingAs($this->seller)
        ->getJson('/api/sales')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'total',
        ]);
});

test('detalhe da venda retorna itens e relacionamentos', function () {
    $saleResponse = $this->actingAs($this->seller)
        ->postJson('/api/sales', [
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]],
        ])->assertCreated();

    $saleId = $saleResponse->json('id');

    $this->actingAs($this->seller)
        ->getJson("/api/sales/{$saleId}")
        ->assertOk()
        ->assertJsonStructure([
            'id', 'total_amount', 'status',
            'items' => [['product_id', 'quantity', 'unit_price', 'product']],
            'seller',
        ]);
});
