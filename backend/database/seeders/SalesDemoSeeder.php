<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Seeder de vendas, clientes e logs de atividade.
 *
 * Gera dados de demonstração distribuídos ao longo dos últimos
 * 12 meses para alimentar gráficos e KPIs do dashboard.
 * Cria clientes, vendas com itens e registros de atividade.
 */
class SalesDemoSeeder extends Seeder
{
    /**
     * Popula vendas distribuídas ao longo de 12 meses.
     *
     * Gera entre 5 e 15 vendas por mês, cada uma com
     * 1 a 4 itens aleatórios do catálogo de produtos.
     */
    public function run(): void
    {
        $sellers = User::whereIn('role', ['admin', 'manager', 'seller'])->get();
        $products = Product::where('is_active', true)->get();

        if ($sellers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Sem vendedores ou produtos. Execute UserSeeder e CategoryAndProductSeeder primeiro.');

            return;
        }

        // Cria clientes de demonstração
        $customers = Customer::factory()->count(20)->create();

        $now = Carbon::now();

        // Gera vendas para os últimos 12 meses
        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = $now->copy()->subMonths($monthsAgo)->startOfMonth();
            $monthEnd = $monthsAgo === 0
                ? $now->copy()
                : $now->copy()->subMonths($monthsAgo)->endOfMonth();

            // Mais vendas nos meses recentes para simular crescimento
            $salesCount = rand(5 + (11 - $monthsAgo), 10 + (11 - $monthsAgo) * 2);

            for ($i = 0; $i < $salesCount; $i++) {
                $seller = $sellers->random();
                $customer = rand(1, 4) <= 3 ? $customers->random() : null;
                $saleDate = Carbon::createFromTimestamp(
                    rand($monthStart->timestamp, $monthEnd->timestamp)
                );

                $sale = Sale::create([
                    'id' => (string) Str::uuid(),
                    'seller_id' => $seller->id,
                    'customer_id' => $customer?->id,
                    'total_amount' => 0,
                    'status' => 'COMPLETED',
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);

                // 1 a 4 itens por venda
                $itemCount = rand(1, 4);
                $saleProducts = $products->random($itemCount);
                $total = 0;

                foreach ($saleProducts as $product) {
                    $qty = rand(1, 5);
                    $unitPrice = (float) $product->sale_price;
                    $subtotal = $qty * $unitPrice;
                    $total += $subtotal;

                    SaleItem::create([
                        'id' => (string) Str::uuid(),
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'created_at' => $saleDate,
                        'updated_at' => $saleDate,
                    ]);
                }

                $sale->update(['total_amount' => $total]);

                // Registra log de atividade
                ActivityLog::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $seller->id,
                    'action' => 'SALE_CREATED',
                    'entity' => 'Sale',
                    'entity_id' => $sale->id,
                    'details' => [
                        'total' => $total,
                        'items_count' => $itemCount,
                        'customer_name' => $customer?->name ?? 'Consumidor final',
                    ],
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);
            }
        }

        $totalSales = Sale::count();
        $this->command->info("Criadas {$totalSales} vendas de demonstração ao longo de 12 meses.");
    }
}
