<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Servico de metricas e indicadores do dashboard.
 *
 * Fornece KPIs com comparativo mensal, dados para gráficos
 * de receita e feed de atividade recente. Consolida informações
 * de produtos, vendas, estoque e logs de atividade.
 */
class DashboardService
{
    /**
     * Retorna KPIs do dashboard com comparativo vs mês anterior.
     *
     * Cada KPI inclui o valor atual e a variação percentual
     * em relação ao mês anterior. Estoque zerado é separado
     * de estoque baixo para tratamento visual diferenciado.
     *
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Receita e vendas do mês atual
        $currentRevenue = (float) Sale::where('created_at', '>=', $thisMonthStart)
            ->where('status', 'COMPLETED')
            ->sum('total_amount');

        $currentSalesCount = Sale::where('created_at', '>=', $thisMonthStart)
            ->where('status', 'COMPLETED')
            ->count();

        // Receita e vendas do mês anterior
        $previousRevenue = (float) Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'COMPLETED')
            ->sum('total_amount');

        $previousSalesCount = Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'COMPLETED')
            ->count();

        // Estoque separado: zerado vs baixo
        $activeProducts = Product::where('is_active', true);
        $zeroStockCount = (clone $activeProducts)
            ->where('stock_quantity', 0)
            ->count();
        $lowStockCount = (clone $activeProducts)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->count();

        return [
            'active_products' => Product::where('is_active', true)->count(),
            'monthly_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'revenue_change' => $this->calculateChange($currentRevenue, $previousRevenue),
            'low_stock_count' => $lowStockCount,
            'zero_stock_count' => $zeroStockCount,
            'monthly_sales_count' => $currentSalesCount,
            'previous_sales_count' => $previousSalesCount,
            'sales_change' => $this->calculateChange($currentSalesCount, $previousSalesCount),
        ];
    }

    /**
     * Retorna dados para o gráfico de receita por período.
     *
     * Suporta três intervalos: últimos 7 dias (por dia),
     * últimos 6 meses (por mês) ou últimos 12 meses (por mês).
     *
     * @param  string  $period  Período: '7d', '6m' ou '12m'
     * @return array<int, array{label: string, value: float}>
     */
    public function getRevenueChart(string $period = '6m'): array
    {
        return match ($period) {
            '7d' => $this->getRevenueByDay(7),
            '12m' => $this->getRevenueByMonth(12),
            default => $this->getRevenueByMonth(6),
        };
    }

    /**
     * Retorna as últimas atividades registradas no sistema.
     *
     * Inclui dados do usuário que realizou a ação e detalhes
     * do evento para exibição no feed de atividade.
     *
     * @param  int  $limit  Quantidade máxima de registros
     * @return Collection<int, ActivityLog>
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        return ActivityLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'user_id', 'action', 'entity', 'details', 'created_at']);
    }

    /**
     * Calcula a variação percentual entre dois valores.
     *
     * @param  float|int  $current  Valor do período atual
     * @param  float|int  $previous  Valor do período anterior
     * @return float|null Variação percentual ou null se não calculável
     */
    private function calculateChange(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Receita agrupada por dia para os últimos N dias.
     *
     * @param  int  $days  Quantidade de dias
     * @return array<int, array{label: string, value: float}>
     */
    private function getRevenueByDay(int $days): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();
        $results = [];

        $salesByDay = Sale::where('created_at', '>=', $start)
            ->where('status', 'COMPLETED')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('Y-m-d');
            $results[] = [
                'label' => $date->format('d/m'),
                'value' => (float) ($salesByDay[$key] ?? 0),
            ];
        }

        return $results;
    }

    /**
     * Receita agrupada por mês para os últimos N meses.
     *
     * @param  int  $months  Quantidade de meses
     * @return array<int, array{label: string, value: float}>
     */
    private function getRevenueByMonth(int $months): array
    {
        $results = [];

        $salesByMonth = Sale::where('created_at', '>=', Carbon::now()->subMonths($months)->startOfMonth())
            ->where('status', 'COMPLETED')
            ->selectRaw("strftime('%Y-%m', created_at) as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $results[] = [
                'label' => $monthNames[$date->month - 1],
                'value' => (float) ($salesByMonth[$key] ?? 0),
            ];
        }

        return $results;
    }
}
