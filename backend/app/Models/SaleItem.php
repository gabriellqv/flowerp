<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Modelo de item de venda.
 *
 * Representa uma linha individual dentro de uma venda,
 * vinculando um produto à quantidade e preço unitário praticados.
 * Possui `cascadeOnDelete`: ao excluir a venda, os itens são removidos.
 *
 * @property string $id UUID gerado automaticamente
 * @property string $sale_id FK -> venda pai
 * @property string $product_id FK -> produto vendido
 * @property int $quantity Quantidade vendida
 * @property string $unit_price Preço unitário no momento da venda (decimal 10,2)
 */
class SaleItem extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price'];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (SaleItem $item) {
            if (empty($item->id)) {
                $item->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Venda à qual este item pertence.
     *
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Produto referenciado neste item.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
