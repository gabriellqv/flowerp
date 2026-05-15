<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller de execucao e consulta de vendas.
 *
 * Centraliza a criacao de vendas (com transacao ACID)
 * e a listagem/consulta de vendas registradas.
 */
class SaleController extends Controller
{
    /**
     * @param  SaleService  $saleService  Servico de logica de vendas
     */
    public function __construct(
        private readonly SaleService $saleService,
    ) {}

    /**
     * Lista vendas com relacionamentos, busca e paginação.
     *
     * @param  Request  $request  Query params: search, page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $sales = Sale::with(['seller:id,name', 'customer:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->search}%"))
                    ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(20);

        return SaleResource::collection($sales);
    }

    /**
     * Executa uma nova venda com validação de estoque.
     *
     * @param  StoreSaleRequest  $request  Dados validados da venda
     * @return JsonResponse Venda criada (201) ou erro de estoque (422)
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $sale = $this->saleService->executeSale(
                $request->validated(),
                $request->user(),
            );

            return response()->json(new SaleResource($sale), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Exibe detalhes de uma venda com itens e relacionamentos.
     *
     * @param  Sale  $sale  Venda resolvida via Route Model Binding
     */
    public function show(Sale $sale): SaleResource
    {
        return new SaleResource($sale->load(['items.product', 'seller', 'customer']));
    }
}
