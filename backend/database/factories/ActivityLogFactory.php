<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory para geracao de logs de atividade de teste.
 *
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define os valores padrao para um novo log de atividade.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['LOGIN', 'LOGOUT', 'SALE_CREATED', 'PRODUCT_CREATED']),
            'entity' => fake()->randomElement(['Product', 'Sale', 'Customer']),
            'entity_id' => (string) Str::uuid(),
        ];
    }
}
