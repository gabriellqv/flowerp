<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona colunas de controle de acesso a tabela de usuarios.
 *
 * Esta migracao insere as colunas para RBAC (role, is_active)
 * e rastreamento do ultimo login (last_login_at).
 */
return new class extends Migration
{
    /**
     * Adiciona as colunas role, is_active e last_login_at.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('viewer')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Remove as colunas de RBAC da tabela de usuarios.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};
