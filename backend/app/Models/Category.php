<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasFactory, HasUuid;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['name'];

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
