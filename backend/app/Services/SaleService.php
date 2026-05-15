<?php

namespace App\Services;

use App\Enums\FinancialEntryCategory;
use App\Enums\FinancialEntryType;
use App\Enums\SaleStatus;
use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\ActivityLog;
use App\Models\FinancialEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Servico de execucao de vendas com transacao ACID.
 *
 * Garante consistencia entre estoque, financeiro e
 * auditoria em uma unica operacao atomica.
 */
class SaleService
{
    /**
     * Executa uma venda completa com transacao ACID.
     *
     * O que acontece dentro da transacao:
     * 1. Valida estoque de cada item (lockForUpdate previne race condition)
     * 2. Decrementa estoque e registra movimentacoes
     * 3. Cria venda e itens com desconto e forma de pagamento
     * 4. Cria entrada financeira (receita) com valor liquido
     * 5. Loga atividade
     *
     * Se qualquer passo falhar, TUDO e revertido.
     *
     * @param  array  $saleData  Dados da venda com customer_id e items
     * @param  User  $seller  Usuario vendedor
     * @return Sale Venda criada com relacionamentos carregados
     *
     * @throws \InvalidArgumentException Quando estoque insuficiente ou produto inativo
     */
    public function executeSale(array $saleData, User $seller): Sale
    {
        return DB::transaction(function () use ($saleData, $seller) {
            $totalAmount = 0;

            foreach ($saleData['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if (! $product->is_active) {
                    throw new \InvalidArgumentException("Produto \"{$product->name}\" esta inativo.");
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \InvalidArgumentException(
                        "Estoque insuficiente para \"{$product->name}\". Disponivel: {$product->stock_quantity}"
                    );
                }

                $totalAmount += $product->sale_price * $item['quantity'];
            }

            foreach ($saleData['items'] as $item) {
                $product = Product::find($item['product_id']);
                $product->decrement('stock_quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => StockMovementType::OUT,
                    'quantity' => $item['quantity'],
                    'reason' => StockMovementReason::SALE,
                ]);
            }

            $sale = Sale::create([
                'seller_id' => $seller->id,
                'customer_id' => $saleData['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'discount' => $saleData['discount'] ?? 0,
                'payment_method' => $saleData['payment_method'] ?? null,
                'status' => SaleStatus::COMPLETED,
            ]);

            $netAmount = $totalAmount - ($saleData['discount'] ?? 0);

            foreach ($saleData['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => Product::find($item['product_id'])->sale_price,
                ]);
            }

            FinancialEntry::create([
                'type' => FinancialEntryType::INCOME,
                'amount' => $netAmount,
                'description' => "Venda #{$sale->id}",
                'category' => FinancialEntryCategory::SALE,
                'sale_id' => $sale->id,
                'is_paid' => true,
                'paid_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $seller->id,
                'action' => 'SALE_CREATED',
                'entity' => 'Sale',
                'entity_id' => $sale->id,
                'details' => [
                    'item_count' => count($saleData['items']),
                    'total_amount' => $totalAmount,
                ],
            ]);

            return $sale->load(['items.product', 'customer']);
        });
    }
}
