<?php

/*
|--------------------------------------------------------------------------
| Testes unitarios do SaleService.
|
| Valida a execucao de venda com transacao ACID, incluindo
| cenarios de sucesso, estoque insuficiente, produto inativo,
| desconto e criacao de registros financeiros e de auditoria.
|--------------------------------------------------------------------------
*/

use App\Enums\FinancialEntryCategory;
use App\Enums\FinancialEntryType;
use App\Enums\SaleStatus;
use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FinancialEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->saleService = new SaleService;
    $this->seller = User::factory()->create(['role' => 'seller']);
    $this->category = Category::factory()->create();

    $this->product1 = Product::factory()->create([
        'category_id' => $this->category->id,
        'sale_price' => 50.00,
        'stock_quantity' => 20,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $this->product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'sale_price' => 30.00,
        'stock_quantity' => 10,
        'min_stock' => 3,
        'is_active' => true,
    ]);
});

test('executeSale conclui venda e decrementa estoque de cada item', function () {
    $saleData = [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 2],
            ['product_id' => $this->product2->id, 'quantity' => 3],
        ],
    ];

    $sale = $this->saleService->executeSale($saleData, $this->seller);

    expect($sale->status)->toBe(SaleStatus::COMPLETED);
    expect($sale->seller_id)->toBe($this->seller->id);
    expect((float) $sale->total_amount)->toBe(190.00); // 2*50 + 3*30

    expect(Product::find($this->product1->id)->stock_quantity)->toBe(18);
    expect(Product::find($this->product2->id)->stock_quantity)->toBe(7);

    expect($sale->items)->toHaveCount(2);
    expect((float) $sale->items->first()->unit_price)->toBe(50.00);
});

test('executeSale lanca excecao para estoque insuficiente e reverte tudo', function () {
    $saleData = [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 25],
        ],
    ];

    expect(fn () => $this->saleService->executeSale($saleData, $this->seller))
        ->toThrow(\InvalidArgumentException::class, 'Estoque insuficiente');

    expect(Product::find($this->product1->id)->stock_quantity)->toBe(20);
    expect(Sale::count())->toBe(0);
    expect(FinancialEntry::count())->toBe(0);
    expect(ActivityLog::count())->toBe(0);
});

test('executeSale lanca excecao para produto inativo', function () {
    Product::where('id', $this->product2->id)->update(['is_active' => false]);

    $saleData = [
        'items' => [
            ['product_id' => $this->product2->id, 'quantity' => 1],
        ],
    ];

    expect(fn () => $this->saleService->executeSale($saleData, $this->seller))
        ->toThrow(\InvalidArgumentException::class, 'inativo');

    expect(Sale::count())->toBe(0);
});

test('executeSale registra movimentacao de estoque para cada item', function () {
    $saleData = [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 5],
            ['product_id' => $this->product2->id, 'quantity' => 2],
        ],
    ];

    $this->saleService->executeSale($saleData, $this->seller);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product1->id,
        'type' => StockMovementType::OUT->value,
        'quantity' => 5,
        'reason' => StockMovementReason::SALE->value,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product2->id,
        'type' => StockMovementType::OUT->value,
        'quantity' => 2,
        'reason' => StockMovementReason::SALE->value,
    ]);
});

test('executeSale cria entrada financeira com valor liquido', function () {
    $saleData = [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 2],
        ],
        'discount' => 10,
    ];

    $sale = $this->saleService->executeSale($saleData, $this->seller);

    $this->assertDatabaseHas('financial_entries', [
        'sale_id' => $sale->id,
        'type' => FinancialEntryType::INCOME->value,
        'amount' => 90.00,
        'category' => FinancialEntryCategory::SALE->value,
        'is_paid' => 1,
    ]);
});

test('executeSale registra log de atividade', function () {
    $saleData = [
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 1],
        ],
    ];

    $sale = $this->saleService->executeSale($saleData, $this->seller);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->seller->id,
        'action' => 'SALE_CREATED',
        'entity' => 'Sale',
        'entity_id' => $sale->id,
    ]);
});

test('executeSale vincula cliente e forma de pagamento quando fornecidos', function () {
    $customer = Customer::factory()->create(['is_active' => true]);

    $saleData = [
        'customer_id' => $customer->id,
        'items' => [
            ['product_id' => $this->product1->id, 'quantity' => 1],
        ],
        'payment_method' => 'pix',
    ];

    $sale = $this->saleService->executeSale($saleData, $this->seller);

    expect($sale->customer_id)->toBe($customer->id);
    expect($sale->payment_method)->toBe('pix');
    expect($sale->customer->id)->toBe($customer->id);
});
