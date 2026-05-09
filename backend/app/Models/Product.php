<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

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
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->id)) {
                $product->id = (string) Str::uuid();
            }
        });
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
