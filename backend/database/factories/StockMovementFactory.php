<?php

namespace Database\Factories;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geracao de movimentacoes de estoque de teste.
 *
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * Define os valores padrao para uma nova movimentacao.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => StockMovementType::IN,
            'quantity' => fake()->numberBetween(1, 50),
            'reason' => StockMovementReason::INITIAL_STOCK,
        ];
    }
}
