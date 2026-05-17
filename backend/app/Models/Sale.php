<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo de venda.
 *
 * Registra uma transação comercial vinculada a um vendedor (User)
 * e opcionalmente a um cliente (Customer). O valor total é calculado
 * a partir dos itens da venda.
 *
 * @property string $id UUID gerado automaticamente
 * @property int $seller_id FK -> usuário vendedor
 * @property string|null $customer_id FK -> cliente (opcional)
 * @property string $total_amount Valor total da venda (decimal 12,2)
 * @property string $discount Desconto aplicado (decimal 12,2)
 * @property string|null $payment_method Forma de pagamento (pix, cash, card, etc.)
 * @property SaleStatus $status Estado da venda
 */
class Sale extends Model
{
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'seller_id', 'customer_id', 'total_amount', 'discount', 'payment_method', 'status',
    ];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'status' => SaleStatus::class,
    ];

    /**
     * Filtra apenas vendas concluídas.
     *
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::COMPLETED);
    }

    /**
     * Filtra vendas do mês atual.
     *
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->startOfMonth());
    }

    /**
     * Vendedor responsável pela venda.
     *
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Cliente associado à venda (pode ser nulo para venda anônima).
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Itens que compõem esta venda.
     *
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Lançamento financeiro gerado por esta venda.
     *
     * @return HasOne<FinancialEntry, $this>
     */
    public function financialEntry(): HasOne
    {
        return $this->hasOne(FinancialEntry::class);
    }
}
