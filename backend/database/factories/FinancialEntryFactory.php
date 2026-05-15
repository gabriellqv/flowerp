<?php

namespace Database\Factories;

use App\Enums\FinancialEntryCategory;
use App\Enums\FinancialEntryType;
use App\Models\FinancialEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geracao de lancamentos financeiros de teste.
 *
 * @extends Factory<FinancialEntry>
 */
class FinancialEntryFactory extends Factory
{
    protected $model = FinancialEntry::class;

    /**
     * Define os valores padrao para um novo lancamento.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => FinancialEntryType::INCOME,
            'amount' => fake()->randomFloat(2, 10, 1000),
            'description' => fake()->sentence(),
            'category' => FinancialEntryCategory::SALE,
            'is_paid' => true,
            'paid_at' => now(),
        ];
    }
}
