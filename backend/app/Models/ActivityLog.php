<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Modelo de log de atividades.
 *
 * Registra ações significativas dos usuários no sistema para
 * auditoria e rastreabilidade (ex.: SALE_CREATED, PRODUCT_UPDATED).
 * O campo `details` armazena metadados adicionais em JSON.
 *
 * @property string $id UUID gerado automaticamente
 * @property int $user_id FK -> usuário que realizou a ação
 * @property string $action Ação executada (ex.: SALE_CREATED)
 * @property string $entity Entidade afetada (ex.: Sale, Product)
 * @property string $entity_id UUID da entidade afetada
 * @property array|null $details Dados complementares em formato JSON
 */
class ActivityLog extends Model
{
    use HasFactory;

    /** @var string Tipo da chave primária (UUID). */
    protected $keyType = 'string';

    /** @var bool Desabilita auto-incremento, utiliza UUID. */
    public $incrementing = false;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = ['user_id', 'action', 'entity', 'entity_id', 'details'];

    /** @var array<string, string> Conversões automáticas de tipo. */
    protected $casts = ['details' => 'array'];

    /**
     * Gera UUID automaticamente ao criar um novo registro.
     */
    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log) {
            if (empty($log->id)) {
                $log->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Usuário que realizou a ação registrada.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
