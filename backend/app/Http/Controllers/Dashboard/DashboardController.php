<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller do dashboard executivo.
 *
 * Exibe KPIs com comparativo mensal, gráficos de receita
 * e feed de atividade recente do time.
 */
class DashboardController extends Controller
{
    /**
     * @param  DashboardService  $dashboardService  Servico de metricas
     */
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Retorna o resumo de metricas do dashboard.
     *
     * Inclui KPIs com variação percentual vs mês anterior
     * e separação de estoque zerado e estoque baixo.
     *
     * @return JsonResponse KPIs em formato JSON
     */
    public function summary(): JsonResponse
    {
        return response()->json($this->dashboardService->getSummary());
    }

    /**
     * Retorna dados para o gráfico de receita.
     *
     * Suporta os períodos: 7d (últimos 7 dias),
     * 6m (últimos 6 meses) e 12m (últimos 12 meses).
     *
     * @param  Request  $request  Requisição com query param ?period=
     * @return JsonResponse Dados do gráfico em formato JSON
     */
    public function revenueChart(Request $request): JsonResponse
    {
        $period = $request->query('period', '6m');

        return response()->json($this->dashboardService->getRevenueChart($period));
    }

    /**
     * Retorna as últimas atividades registradas.
     *
     * @return JsonResponse Feed de atividade em formato JSON
     */
    public function activityFeed(): JsonResponse
    {
        return response()->json($this->dashboardService->getRecentActivity());
    }
}
