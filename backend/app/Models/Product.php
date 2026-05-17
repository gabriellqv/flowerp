<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de produto do catálogo.
 *
 * Armazena informações de precificação, estoque e status.
 * Utiliza `decimal(10,2)` para valores monetários, evitando
 * erros de arredondamento comuns em `float`.
 *
 * @property string $id UUID gerado automaticamente
 * @property string $category_id FK -> categorias
 * @property string $name Nome do produto (máx. 200 caracteres)
 * @property string $sku Código único de identificação
 * @property string|null $description Descrição opcional
 * @property string $cost_price Preço de custo (decimal 10,2)
 * @property string $sale_price Preço de venda (decimal 10,2)
 * @property int $stock_quantity Quantidade atual em estoque
 * @property int $min_stock Estoque mínimo para alerta
 * @property bool $is_active Indica se o produto está disponível
 */
class Product extends Model
{
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'category_id', 'name', 'sku', 'description',
        'cost_price', 'sale_price', 'stock_quantity',
        'min_stock', 'is_active',
    ];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Filtra apenas produtos ativos.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtra produtos ativos com estoque abaixo do mínimo.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    /**
     * Filtra produtos por categoria.
     *
     * @param  Builder<Product>  $query
     * @param  string  $categoryId  UUID da categoria
     * @return Builder<Product>
     */
    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Categoria à qual este produto pertence.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Itens de venda que referenciam este produto.
     *
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Movimentações de estoque associadas a este produto.
     *
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
