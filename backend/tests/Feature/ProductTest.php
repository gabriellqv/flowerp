<?php

/*
|--------------------------------------------------------------------------
| Testes de CRUD de produtos e logica de estoque.
|
| Valida criacao com movimentacao inicial, validacao de campos
| obrigatorios, regras de unicidade (SKU), filtros de busca,
| toggle de status, exclusao em lote e alertas de estoque baixo.
|--------------------------------------------------------------------------
*/

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->category = Category::factory()->create();
});

// ==========================================
// Criacao de produtos
// ==========================================

test('cria produto e registra movimentacao de estoque inicial', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Teclado Mecanico RGB',
            'sku' => 'SKU-TEC-001',
            'category_id' => $this->category->id,
            'cost_price' => 120.00,
            'sale_price' => 250.00,
            'stock_quantity' => 30,
            'min_stock' => 5,
        ])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Teclado Mecanico RGB'])
        ->assertJsonPath('stock_quantity', 30);

    // Movimentacao de estoque inicial registrada
    $this->assertDatabaseHas('stock_movements', [
        'type' => 'IN',
        'quantity' => 30,
        'reason' => 'INITIAL_STOCK',
    ]);
});

test('cria produto com estoque zero sem registrar movimentacao', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Produto Futuro',
            'sku' => 'SKU-FUT-001',
            'category_id' => $this->category->id,
            'cost_price' => 50.00,
            'sale_price' => 100.00,
            'stock_quantity' => 0,
        ])
        ->assertCreated();

    // Nao deve criar movimentacao quando estoque e zero
    $this->assertDatabaseMissing('stock_movements', [
        'reason' => 'INITIAL_STOCK',
    ]);
});

// ==========================================
// Validacao de campos
// ==========================================

test('rejeita produto sem campos obrigatorios', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'sku', 'category_id', 'cost_price', 'sale_price', 'stock_quantity']);
});

test('rejeita produto com SKU duplicado', function () {
    Product::factory()->create(['sku' => 'SKU-DUP-001']);

    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Produto Duplicado',
            'sku' => 'SKU-DUP-001',
            'category_id' => $this->category->id,
            'cost_price' => 10.00,
            'sale_price' => 20.00,
            'stock_quantity' => 5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('sku');
});

test('rejeita produto com preco de venda menor que custo', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Prejuizo Garantido',
            'sku' => 'SKU-PREJ-001',
            'category_id' => $this->category->id,
            'cost_price' => 100.00,
            'sale_price' => 50.00,
            'stock_quantity' => 10,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('sale_price');
});

test('rejeita produto com categoria inexistente', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Produto Orfao',
            'sku' => 'SKU-ORF-001',
            'category_id' => '00000000-0000-0000-0000-000000000000',
            'cost_price' => 10.00,
            'sale_price' => 20.00,
            'stock_quantity' => 5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('category_id');
});

test('rejeita produto com nome menor que 3 caracteres', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'AB',
            'sku' => 'SKU-MIN-001',
            'category_id' => $this->category->id,
            'cost_price' => 10.00,
            'sale_price' => 20.00,
            'stock_quantity' => 5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

// ==========================================
// Atualizacao
// ==========================================

test('atualiza produto com dados parciais', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Nome Antigo',
        'sale_price' => 50.00,
        'cost_price' => 20.00,
    ]);

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Nome Novo',
            'cost_price' => 20.00,
            'sale_price' => 75.00,
        ])
        ->assertOk()
        ->assertJsonFragment(['name' => 'Nome Novo']);

    expect($product->fresh()->sale_price)->toBe('75.00');
});

// ==========================================
// Listagem e filtros
// ==========================================

test('listagem retorna somente produtos ativos', function () {
    Product::factory()->create(['is_active' => true, 'category_id' => $this->category->id]);
    Product::factory()->create(['is_active' => false, 'category_id' => $this->category->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products');

    $response->assertOk();
    $data = $response->json('data');
    expect(count($data))->toBe(1);
});

test('busca por nome funciona', function () {
    $p1 = Product::factory()->create([
        'category_id' => $this->category->id,
    ]);
    $p1->update(['name' => 'Monitor Ultrawide']);

    $p2 = Product::factory()->create([
        'category_id' => $this->category->id,
    ]);
    $p2->update(['name' => 'Teclado Gamer']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products?search=Monitor');

    $data = $response->json('data');
    expect(count($data))->toBe(1);
    expect($data[0]['name'])->toBe('Monitor Ultrawide');
});

test('filtro por categoria funciona', function () {
    $cat2 = Category::factory()->create();
    Product::factory()->create(['category_id' => $this->category->id]);
    Product::factory()->create(['category_id' => $cat2->id]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/products?category_id={$this->category->id}");

    $data = $response->json('data');
    expect(count($data))->toBe(1);
});

// ==========================================
// Toggle e bulk operations
// ==========================================

test('toggle alterna is_active do produto', function () {
    $product = Product::factory()->create([
        'is_active' => true,
        'category_id' => $this->category->id,
    ]);

    $this->actingAs($this->admin)
        ->patchJson("/api/products/{$product->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('is_active', false);

    // Alterna de volta
    $this->actingAs($this->admin)
        ->patchJson("/api/products/{$product->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('is_active', true);
});

test('exclusao em lote desativa multiplos produtos', function () {
    $products = Product::factory()->count(3)->create([
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);

    $ids = $products->pluck('id')->toArray();

    $this->actingAs($this->admin)
        ->postJson('/api/products/bulk-delete', ['ids' => $ids])
        ->assertNoContent();

    // Todos devem estar inativos
    foreach ($ids as $id) {
        expect(Product::find($id)->is_active)->toBeFalse();
    }
});

test('exclusao em lote rejeita array vazio', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products/bulk-delete', ['ids' => []])
        ->assertStatus(422);
});

// ==========================================
// Estoque baixo
// ==========================================

test('retorna produtos com estoque abaixo do minimo', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 2,
        'min_stock' => 10,
        'is_active' => true,
    ]);
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products/low-stock');

    $response->assertOk();
    $data = $response->json();
    expect(count($data))->toBe(1);
    expect($data[0]['stock_quantity'])->toBe(2);
});

test('produto com estoque zerado aparece como estoque baixo', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 0,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products/low-stock');

    $response->assertOk();
    expect(count($response->json()))->toBe(1);
});

test('produto inativo nao aparece em estoque baixo', function () {
    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_quantity' => 0,
        'min_stock' => 5,
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products/low-stock');

    $response->assertOk();
    expect(count($response->json()))->toBe(0);
});

// ==========================================
// Detalhe e show
// ==========================================

test('show retorna produto com categoria', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
    ]);

    $this->actingAs($this->admin)
        ->getJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'sku', 'category']);
});
