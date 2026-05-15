<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de cliente.
 *
 * Representa uma pessoa física ou jurídica que realiza compras.
 * O campo `document` armazena CPF ou CNPJ de forma unificada.
 *
 * @property string $id UUID gerado automaticamente
 * @property string $name Nome completo ou razão social
 * @property string|null $email E-mail de contato
 * @property string|null $phone Telefone de contato
 * @property string|null $document CPF/CNPJ (único, opcional)
 * @property bool $is_active Indica se o cliente está ativo
 */
class Customer extends Model
{
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['name', 'email', 'phone', 'document', 'is_active'];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = ['is_active' => 'boolean'];

    /**
     * Filtra apenas clientes ativos.
     *
     * @param  Builder<Customer>  $query
     * @return Builder<Customer>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Vendas realizadas para este cliente.
     *
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
