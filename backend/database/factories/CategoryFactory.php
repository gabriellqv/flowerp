<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de categorias de teste.
 *
 * Utiliza uma lista fixa de 10 nomes de categorias em pt-BR
 * para garantir dados realistas e sem repetição (via `unique()`).
 *
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define os valores padrão para uma nova categoria.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Eletrônicos', 'Móveis', 'Limpeza', 'Alimentos',
                'Bebidas', 'Papelaria', 'Ferramentas', 'Informática',
                'Vestuário', 'Higiene',
            ]),
        ];
    }
}
