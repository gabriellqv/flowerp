<?php

namespace App\Models;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['product_id', 'type', 'quantity', 'reason'];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'type' => StockMovementType::class,
        'reason' => StockMovementReason::class,
    ];

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
