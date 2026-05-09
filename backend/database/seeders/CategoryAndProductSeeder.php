<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeder de categorias e produtos.
 *
 * Cria 8 categorias e distribui 15 produtos em cada uma,
 * totalizando 120 produtos com dados realistas de precificação
 * e estoque para demonstração e testes.
 */
class CategoryAndProductSeeder extends Seeder
{
    /**
     * Popula categorias e seus respectivos produtos.
     */
    public function run(): void
    {
        $categories = Category::factory()->count(8)->create();

        foreach ($categories as $category) {
            Product::factory()
                ->count(15)
                ->create(['category_id' => $category->id]);
        }
    }
}
