<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geracao de vendas de teste.
 *
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define os valores padrao para uma nova venda.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => User::factory(),
            'total_amount' => fake()->randomFloat(2, 50, 5000),
            'discount' => 0,
            'status' => SaleStatus::COMPLETED,
        ];
    }
}
