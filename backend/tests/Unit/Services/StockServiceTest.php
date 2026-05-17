<?php

/*
|--------------------------------------------------------------------------
| Testes unitários do StockService.
|
| Garante que a lógica de negócio do serviço de estoque
| funcione isoladamente, especialmente as regras de
| criação com movimentação e alertas de estoque baixo.
|--------------------------------------------------------------------------
*/

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->stockService = new StockService;
    $this->category = Category::factory()->create();
});

test('createProduct cria produto e registra movimentacao inicial positiva', function () {
    $data = [
        'name' => 'Produto de Teste',
        'sku' => 'TEST-001',
        'category_id' => $this->category->id,
        'cost_price' => 10.00,
        'sale_price' => 20.00,
        'stock_quantity' => 15,
        'min_stock' => 5,
    ];

    $product = $this->stockService->createProduct($data);

    expect($product->id)->not->toBeNull();
    expect($product->stock_quantity)->toBe(15);
    expect($product->relationLoaded('category'))->toBeTrue();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::IN->value,
        'quantity' => 15,
        'reason' => StockMovementReason::INITIAL_STOCK->value,
    ]);
});

test('createProduct nao registra movimentacao se estoque for zero', function () {
    $data = [
        'name' => 'Produto Sem Estoque',
        'sku' => 'TEST-002',
        'category_id' => $this->category->id,
        'cost_price' => 10.00,
        'sale_price' => 20.00,
        'stock_quantity' => 0,
        'min_stock' => 5,
    ];

    $product = $this->stockService->createProduct($data);

    $this->assertDatabaseMissing('stock_movements', [
        'product_id' => $product->id,
        'reason' => StockMovementReason::INITIAL_STOCK->value,
    ]);
});

test('updateProduct atualiza os campos e carrega a categoria', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Antigo',
        'sale_price' => 50.00,
    ]);

    $updatedProduct = $this->stockService->updateProduct($product, [
        'name' => 'Novo',
        'sale_price' => 75.00,
    ]);

    expect($updatedProduct->name)->toBe('Novo');
    expect($updatedProduct->sale_price)->toBe('75.00'); // Cast decimal
    expect($updatedProduct->relationLoaded('category'))->toBeTrue();
});

test('getLowStockProducts retorna produtos abaixo do minimo', function () {
    Product::factory()->create(['stock_quantity' => 20, 'min_stock' => 5]); // OK
    $lowStock = Product::factory()->create(['stock_quantity' => 2, 'min_stock' => 10]); // Baixo

    $products = $this->stockService->getLowStockProducts();

    expect($products->count())->toBe(1);
    expect($products->first()->id)->toBe($lowStock->id);
});

test('toggleProductActive alterna status do produto', function () {
    $product = Product::factory()->create([
        'is_active' => true,
        'category_id' => $this->category->id,
    ]);

    $toggled = $this->stockService->toggleProductActive($product);
    expect($toggled->is_active)->toBeFalse();

    $toggledAgain = $this->stockService->toggleProductActive($toggled);
    expect($toggledAgain->is_active)->toBeTrue();
});
