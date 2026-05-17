<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal da aplicação.
 *
 * Orquestra a execução dos seeders na ordem correta,
 * respeitando dependências entre tabelas (usuários antes de vendas).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Executa todos os seeders registrados.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategoryAndProductSeeder::class,
            SalesDemoSeeder::class,
        ]);
    }
}
