<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo de usuário do sistema.
 *
 * Representa um colaborador autenticado via Sanctum com controle
 * de perfil (role) e status de ativação. Cada usuário pode
 * atuar como vendedor e possuir registros de atividade.
 *
 * @property int $id Identificador auto-incremento
 * @property string $name Nome completo do colaborador
 * @property string $email E-mail único (usado como login)
 * @property string $password Senha criptografada via bcrypt
 * @property string $role Perfil de acesso: admin, manager, seller, viewer
 * @property bool $is_active Indica se o usuário pode acessar o sistema
 * @property Carbon|null $last_login_at Último acesso registrado
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** @var array<int, string> Campos permitidos para atribuição em massa. */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /** @var array<int, string> Campos ocultos na serialização JSON. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define as conversões automáticas de tipo (casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Vendas realizadas por este usuário (como vendedor).
     *
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    /**
     * Registros de atividade associados a este usuário.
     *
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
