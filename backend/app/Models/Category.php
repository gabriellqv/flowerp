<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Modelo de categoria de produtos.
 *
 * Agrupa produtos em segmentos para facilitar a navegação,
 * filtragem e relatórios por área (ex.: Eletrônicos, Alimentos).
 *
 * @property string $id UUID gerado automaticamente na criação
 * @property string $name Nome único da categoria (máx. 100 caracteres)
 */
class Category extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['name'];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->id)) {
                $category->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Produtos pertencentes a esta categoria.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
