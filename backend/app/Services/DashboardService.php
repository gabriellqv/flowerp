<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;

/**
 * Servico de metricas e indicadores do dashboard.
 *
 * Fornece KPIs para a tela inicial do sistema,
 * consolidando dados de produtos, vendas e estoque.
 */
class DashboardService
{
    /**
     * Retorna KPIs do dashboard executivo.
     *
     * Consolida metricas de produtos ativos, receita mensal,
     * alertas de estoque baixo e total de vendas do mes corrente.
     *
     * @return array<string, int|float>
     */
    public function getSummary(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'active_products' => Product::where('is_active', true)->count(),
            'monthly_revenue' => (float) Sale::where('created_at', '>=', $thisMonth)
                ->where('status', 'COMPLETED')
                ->sum('total_amount'),
            'low_stock_count' => Product::where('is_active', true)
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count(),
            'monthly_sales_count' => Sale::where('created_at', '>=', $thisMonth)
                ->where('status', 'COMPLETED')
                ->count(),
        ];
    }
}
