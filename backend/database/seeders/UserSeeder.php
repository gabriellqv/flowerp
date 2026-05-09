<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder de usuários do sistema.
 *
 * Cria 2 usuários com credenciais fixas para testes (admin e gerente)
 * e 10 usuários aleatórios com perfis variados.
 *
 * Credenciais de teste:
 * - admin@flowerp.com  / senha123 (perfil: admin)
 * - gerente@flowerp.com / senha123 (perfil: manager)
 */
class UserSeeder extends Seeder
{
    /**
     * Popula a tabela de usuários com dados iniciais.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin FlowERP',
            'email' => 'admin@flowerp.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Gerente',
            'email' => 'gerente@flowerp.com',
            'role' => 'manager',
        ]);

        User::factory()->count(10)->create();
    }
}
