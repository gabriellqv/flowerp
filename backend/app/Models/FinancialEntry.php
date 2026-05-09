<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Modelo de lançamento financeiro.
 *
 * Registra receitas (INCOME) e despesas (EXPENSE) do negócio.
 * Pode estar vinculado a uma venda para rastreabilidade automática.
 * O campo `is_paid` controla o fluxo de caixa (previsto vs. realizado).
 *
 * @property string $id UUID gerado automaticamente
 * @property string $type Tipo: INCOME (receita) ou EXPENSE (despesa)
 * @property string $amount Valor do lançamento (decimal 12,2)
 * @property string $description Descrição do lançamento (máx. 500 caracteres)
 * @property string|null $category Classificação: SALE, PURCHASE, OTHER
 * @property string|null $sale_id FK -> venda associada (opcional)
 * @property bool $is_paid Indica se o lançamento foi efetivado
 * @property Carbon|null $paid_at Data/hora do pagamento
 */
class FinancialEntry extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'type', 'amount', 'description', 'category',
        'sale_id', 'is_paid', 'paid_at',
    ];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (FinancialEntry $entry) {
            if (empty($entry->id)) {
                $entry->id = (string) Str::uuid();
            }
        });
    }

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
