<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait para geração automática de UUID como chave primária.
 *
 * Configura o model para utilizar UUID (string) em vez de
 * auto-incremento numérico. O UUID é gerado automaticamente
 * no evento `creating` caso a chave esteja vazia.
 *
 * Uso: `use HasUuid;` dentro do model (substitui $keyType,
 * $incrementing e booted() manuais).
 */
trait HasUuid
{
    /**
     * Retorna o tipo da chave primária como string (UUID).
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Desabilita auto-incremento para compatibilidade com UUID.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
