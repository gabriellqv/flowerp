<?php

namespace App\Models;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Modelo de movimentação de estoque.
 *
 * Registra cada entrada (IN) ou saída (OUT) de um produto,
 * mantendo rastreabilidade completa do motivo da alteração.
 *
 * @property string $id UUID gerado automaticamente
 * @property string $product_id FK -> produto movimentado
 * @property StockMovementType $type Direção: IN (entrada) ou OUT (saída)
 * @property int $quantity Quantidade movimentada
 * @property StockMovementReason $reason Motivo da movimentação
 */
class StockMovement extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['product_id', 'type', 'quantity', 'reason'];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'type' => StockMovementType::class,
        'reason' => StockMovementReason::class,
    ];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            if (empty($movement->id)) {
                $movement->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Produto associado a esta movimentação.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
