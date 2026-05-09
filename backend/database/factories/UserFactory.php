<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory para geração de usuários de teste.
 *
 * Todos os usuários gerados utilizam a senha padrão `senha123`
 * para facilitar testes em ambiente de desenvolvimento.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define os valores padrão para um novo usuário.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('senha123'),
            'role' => fake()->randomElement(['admin', 'manager', 'seller', 'viewer']),
            'is_active' => true,
        ];
    }
}
