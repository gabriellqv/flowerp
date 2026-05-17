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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Exporta todas as vendas para formato CSV.
     *
     * Utiliza streamDownload para gerar o arquivo em memória,
     * sem gravar fisicamente no disco.
     *
     * @return StreamedResponse
     */
    public function export()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // BOM para Excel ler acentos
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeçalho
            fputcsv($handle, ['ID', 'Data', 'Vendedor', 'Cliente', 'Status', 'Total', 'Desconto'], ';');

            $sales = Sale::with(['seller', 'customer'])->latest()->get();

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->id,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->seller?->name ?? 'N/A',
                    $sale->customer?->name ?? 'Avulso',
                    $sale->status->value,
                    number_format($sale->total_amount, 2, ',', '.'),
                    number_format($sale->discount, 2, ',', '.'),
                ], ';');
            }

            fclose($handle);
        }, 'vendas_export_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
