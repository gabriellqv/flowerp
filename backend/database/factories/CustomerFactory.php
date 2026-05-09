<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de clientes de teste.
 *
 * Gera dados fictícios com CPF formatado (###.###.###-##)
 * para simular cadastros realistas em ambiente de desenvolvimento.
 *
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define os valores padrão para um novo cliente.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'document' => fake()->unique()->numerify('###.###.###-##'),
            'is_active' => true,
        ];
    }
}
