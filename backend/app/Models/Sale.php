<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

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
 * @property string $status Estado: COMPLETED ou CANCELLED
 */
class Sale extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'seller_id', 'customer_id', 'total_amount', 'status',
    ];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->id)) {
                $sale->id = (string) Str::uuid();
            }
        });
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
