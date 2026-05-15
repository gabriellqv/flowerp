<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        return Cache::remember('dashboard.summary', now()->addHour(), function () {
            $thisMonthStart = Carbon::now()->startOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Receita e vendas do mês atual
            $currentRevenue = (float) Sale::thisMonth()
                ->completed()
                ->sum('total_amount');

            $currentSalesCount = Sale::thisMonth()
                ->completed()
                ->count();

            // Receita e vendas do mês anterior
            $previousRevenue = (float) Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->where('status', SaleStatus::COMPLETED)
                ->sum('total_amount');

            $previousSalesCount = Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->where('status', SaleStatus::COMPLETED)
                ->count();

            // Estoque separado: zerado vs baixo
            $activeProducts = Product::active();
            $zeroStockCount = (clone $activeProducts)
                ->where('stock_quantity', 0)
                ->count();
            $lowStockCount = (clone $activeProducts)
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->count();

            // Valor total em estoque (baseado no preço de custo)
            $totalStockValue = (float) Product::active()
                ->sum(DB::raw('stock_quantity * cost_price'));

            // Ticket medio do mes atual
            $averageTicket = $currentSalesCount > 0 ? $currentRevenue / $currentSalesCount : 0;

            return [
                'active_products' => Product::active()->count(),
                'monthly_revenue' => $currentRevenue,
                'previous_revenue' => $previousRevenue,
                'revenue_change' => $this->calculateChange($currentRevenue, $previousRevenue),
                'low_stock_count' => $lowStockCount,
                'zero_stock_count' => $zeroStockCount,
                'monthly_sales_count' => $currentSalesCount,
                'previous_sales_count' => $previousSalesCount,
                'sales_change' => $this->calculateChange($currentSalesCount, $previousSalesCount),
                'average_ticket' => $averageTicket,
                'total_stock_value' => $totalStockValue,
            ];
        });
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
        return Cache::remember("dashboard.revenue_chart.{$period}", now()->addHour(), function () use ($period) {
            return match ($period) {
                '7d' => $this->getRevenueByDay(7),
                '12m' => $this->getRevenueByMonth(12),
                default => $this->getRevenueByMonth(6),
            };
        });
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
     * Retorna atividades paginadas com suporte a busca e filtro.
     *
     * Permite buscar por nome do usuário e filtrar por tipo de ação.
     * Utilizado na página de histórico completo de atividades.
     *
     * @param  Request  $request  Requisição com query params (search, action, per_page)
     */
    public function getPaginatedActivity(Request $request): LengthAwarePaginator
    {
        $query = ActivityLog::with('user:id,name')
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        $perPage = (int) $request->query('per_page', 15);

        return $query->paginate($perPage, ['id', 'user_id', 'action', 'entity', 'details', 'created_at']);
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
            ->completed()
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
            ->completed()
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

    /**
     * Retorna os 5 produtos mais vendidos no mes atual.
     *
     * @return Collection<int, mixed>
     */
    public function getTopProducts(): Collection
    {
        return Cache::remember('dashboard.top_products', now()->addHour(), function () {
            $thisMonthStart = Carbon::now()->startOfMonth();

            return DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.created_at', '>=', $thisMonthStart)
                ->where('sales.status', SaleStatus::COMPLETED)
                ->select('products.name as label', DB::raw('SUM(sale_items.quantity) as value'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('value')
                ->limit(5)
                ->get();
        });
    }

    /**
     * Retorna a receita do mes atual agrupada por categoria.
     *
     * @return Collection<int, mixed>
     */
    public function getRevenueByCategory(): Collection
    {
        return Cache::remember('dashboard.revenue_by_category', now()->addHour(), function () {
            $thisMonthStart = Carbon::now()->startOfMonth();

            return DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.created_at', '>=', $thisMonthStart)
                ->where('sales.status', SaleStatus::COMPLETED)
                ->select('categories.name as label', DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as value'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('value')
                ->get();
        });
    }

    /**
     * Invalida todos os caches do dashboard.
     *
     * Deve ser chamado sempre que uma venda for realizada
     * ou o estoque sofrer movimentações.
     */
    public function clearCache(): void
    {
        Cache::forget('dashboard.summary');
        Cache::forget('dashboard.revenue_chart.7d');
        Cache::forget('dashboard.revenue_chart.6m');
        Cache::forget('dashboard.revenue_chart.12m');
        Cache::forget('dashboard.top_products');
        Cache::forget('dashboard.revenue_by_category');
    }
}
