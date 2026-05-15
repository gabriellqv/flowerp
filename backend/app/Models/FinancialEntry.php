<?php

namespace App\Models;

use App\Enums\FinancialEntryCategory;
use App\Enums\FinancialEntryType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modelo de lançamento financeiro.
 *
 * Registra receitas (INCOME) e despesas (EXPENSE) do negócio.
 * Pode estar vinculado a uma venda para rastreabilidade automática.
 * O campo `is_paid` controla o fluxo de caixa (previsto vs. realizado).
 *
 * @property string $id UUID gerado automaticamente
 * @property FinancialEntryType $type Tipo do lançamento financeiro
 * @property string $amount Valor do lançamento (decimal 12,2)
 * @property string $description Descrição do lançamento (máx. 500 caracteres)
 * @property FinancialEntryCategory|null $category Categoria do lançamento
 * @property string|null $sale_id FK -> venda associada (opcional)
 * @property bool $is_paid Indica se o lançamento foi efetivado
 * @property Carbon|null $paid_at Data/hora do pagamento
 */
class FinancialEntry extends Model
{
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'type', 'amount', 'description', 'category',
        'sale_id', 'is_paid', 'paid_at',
    ];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'type' => FinancialEntryType::class,
        'category' => FinancialEntryCategory::class,
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Venda que originou este lançamento (quando aplicável).
     *
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
