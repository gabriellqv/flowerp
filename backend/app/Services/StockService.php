<?php

namespace App\Services;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Servico de gerenciamento de estoque e catalogo de produtos.
 *
 * Centraliza a logica de negocio para listagem, criacao,
 * atualizacao e alertas de estoque baixo.
 */
class StockService
{
    /**
     * Lista produtos com busca, filtro por categoria, ordenacao e paginacao.
     *
     * @param  string|null  $search  Busca por nome ou SKU
     * @param  string|null  $categoryId  Filtrar por categoria
     * @param  string  $sortBy  Campo de ordenacao
     * @param  string  $order  'asc' ou 'desc'
     * @param  int  $perPage  Itens por pagina
     */
    public function listProducts(
        ?string $search = null,
        ?string $categoryId = null,
        string $sortBy = 'created_at',
        string $order = 'desc',
        int $perPage = 20,
    ): LengthAwarePaginator {
        return Product::query()
            ->with('category')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($q) => $q->byCategory($categoryId))
            ->active()
            ->orderBy($sortBy, $order)
            ->paginate($perPage);
    }

    /**
     * Cria produto e registra movimentação de estoque inicial.
     *
     * Executado dentro de uma transação: ou salva tudo, ou nada.
     *
     * @param  array{
     *   name: string,
     *   sku: string,
     *   description?: string,
     *   category_id: string,
     *   cost_price: float,
     *   sale_price: float,
     *   stock_quantity: int,
     *   min_stock?: int
     * }  $data  Dados validados do produto
     * @return Product Produto criado com categoria carregada
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'],
                'cost_price' => $data['cost_price'],
                'sale_price' => $data['sale_price'],
                'stock_quantity' => $data['stock_quantity'],
                'min_stock' => $data['min_stock'] ?? 5,
            ]);

            if ($data['stock_quantity'] > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => StockMovementType::IN,
                    'quantity' => $data['stock_quantity'],
                    'reason' => StockMovementReason::INITIAL_STOCK,
                ]);
            }

            app(DashboardService::class)->clearCache();

            return $product->load('category');
        });
    }

    /**
     * Atualiza um produto com os dados validados.
     *
     * @param  Product  $product  Produto a ser atualizado
     * @param  array{
     *   name?: string,
     *   sku?: string,
     *   description?: string,
     *   category_id?: string,
     *   cost_price?: float,
     *   sale_price?: float,
     *   stock_quantity?: int,
     *   min_stock?: int
     * }  $data  Dados validados a serem aplicados
     * @return Product Produto atualizado com categoria carregada
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        app(DashboardService::class)->clearCache();

        return $product->load('category');
    }

    /**
     * Remove logicamente um produto (soft delete via is_active).
     *
     * @param  Product  $product  Produto a ser desativado
     */
    public function deactivateProduct(Product $product): void
    {
        $product->update(['is_active' => false]);
        app(DashboardService::class)->clearCache();
    }

    /**
     * Retorna produtos com estoque abaixo do minimo configurado.
     *
     * @return Collection
     */
    public function getLowStockProducts()
    {
        return Product::query()
            ->lowStock()
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Alterna o status is_active de um produto.
     *
     * @param  Product  $product  Produto a ter o estado alternado
     * @return Product Produto atualizado com categoria carregada
     */
    public function toggleProductActive(Product $product): Product
    {
        $product->update(['is_active' => ! $product->is_active]);

        app(DashboardService::class)->clearCache();

        return $product->load('category');
    }

    /**
     * Desativa multiplos produtos em lote.
     *
     * @param  array  $ids  Array de UUIDs dos produtos
     */
    public function bulkDeactivateProducts(array $ids): void
    {
        Product::whereIn('id', $ids)->update(['is_active' => false]);
        app(DashboardService::class)->clearCache();
    }
}
