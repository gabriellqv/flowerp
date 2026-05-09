<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

/**
 * Controller do dashboard executivo.
 *
 * Exibe KPIs consolidadas do negocio: produtos ativos,
 * receita mensal, alertas de estoque e total de vendas.
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
     * @return JsonResponse KPIs em formato JSON
     */
    public function summary(): JsonResponse
    {
        return response()->json($this->dashboardService->getSummary());
    }
}
