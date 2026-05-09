<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['name', 'email', 'phone', 'document', 'is_active'];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = ['is_active' => 'boolean'];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->id)) {
                $customer->id = (string) Str::uuid();
            }
        });
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
