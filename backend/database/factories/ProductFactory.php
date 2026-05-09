<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de produtos de teste.
 *
 * Gera preços de custo aleatórios entre R$ 5,00 e R$ 500,00,
 * e aplica uma margem de lucro entre 20% e 150% para o preço
 * de venda, simulando cenários comerciais realistas.
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define os valores padrão para um novo produto.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 5, 500);

        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####')),
            'cost_price' => $cost,
            'sale_price' => $cost * fake()->randomFloat(2, 1.2, 2.5),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'min_stock' => fake()->numberBetween(5, 20),
            'is_active' => true,
        ];
    }
}
