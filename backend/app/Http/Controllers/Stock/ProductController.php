<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller de gerenciamento de produtos.
 *
 * Fornece endpoints REST para CRUD de produtos,
 * alem de listagem de estoque baixo e filtros avancados.
 */
class ProductController extends Controller
{
    /**
     * @param  StockService  $stockService  Servico de logica de estoque
     */
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Lista produtos com filtros, ordenacao e paginacao.
     *
     * @param  Request  $request  Query params: search, category_id, sort_by, order, per_page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->stockService->listProducts(
            search: $request->query('search'),
            categoryId: $request->query('category_id'),
            sortBy: $request->query('sort_by', 'created_at'),
            order: $request->query('order', 'desc'),
            perPage: (int) $request->query('per_page', 20),
        );

        return ProductResource::collection($products);
    }

    /**
     * Cria um novo produto com movimentacao inicial de estoque.
     *
     * @param  StoreProductRequest  $request  Dados validados do produto
     * @return JsonResponse Produto criado (201)
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->stockService->createProduct($request->validated());

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Exibe os detalhes de um produto especifico.
     *
     * @param  Product  $product  Produto resolvido via Route Model Binding
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    /**
     * Atualiza dados de um produto existente.
     *
     * @param  UpdateProductRequest  $request  Dados validados (parcial)
     * @param  Product  $product  Produto a ser atualizado
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->stockService->updateProduct($product, $request->validated());

        return new ProductResource($product);
    }

    /**
     * Desativa logicamente um produto (soft delete).
     *
     * @param  Product  $product  Produto a ser desativado
     * @return JsonResponse 204 No Content
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->stockService->deactivateProduct($product);

        return response()->json(null, 204);
    }

    /**
     * Lista produtos com estoque abaixo do minimo configurado.
     */
    public function lowStock(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->stockService->getLowStockProducts());
    }

    /**
     * Alterna o status ativo/inativo de um produto.
     *
     * @param  Product  $product  Produto a ser alternado
     * @return JsonResponse Produto com novo estado
     */
    public function toggleActive(Product $product): ProductResource
    {
        $product = $this->stockService->toggleProductActive($product);

        return new ProductResource($product);
    }

    /**
     * Exclui multiplos produtos em lote.
     *
     * @param  Request  $request  Deve conter array 'ids' com UUIDs
     * @return JsonResponse 204 No Content
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['string', 'uuid']]);

        $this->stockService->bulkDeactivateProducts($request->input('ids'));

        return response()->json(null, 204);
    }
}
