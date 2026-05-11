<?php

/*
|--------------------------------------------------------------------------
| Testes de RBAC (Role-Based Access Control).
|
| Valida que o RoleMiddleware bloqueia ou permite acesso
| a endpoints protegidos com base no perfil do usuario.
| Cada teste verifica um cenario especifico de permissao.
|--------------------------------------------------------------------------
*/

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

// ==========================================
// Produtos: permissoes de criacao
// ==========================================

test('admin pode criar produto', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->postJson('/api/products', [
            'name' => 'Produto RBAC Admin',
            'sku' => 'SKU-RBAC-001',
            'category_id' => $category->id,
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'stock_quantity' => 50,
        ])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Produto RBAC Admin']);
});

test('manager pode criar produto', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $category = Category::factory()->create();

    $this->actingAs($manager)
        ->postJson('/api/products', [
            'name' => 'Produto RBAC Manager',
            'sku' => 'SKU-RBAC-002',
            'category_id' => $category->id,
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'stock_quantity' => 30,
        ])
        ->assertCreated();
});

test('seller nao pode criar produto', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $category = Category::factory()->create();

    $this->actingAs($seller)
        ->postJson('/api/products', [
            'name' => 'Produto Bloqueado',
            'sku' => 'SKU-RBAC-003',
            'category_id' => $category->id,
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'stock_quantity' => 10,
        ])
        ->assertStatus(403)
        ->assertJsonFragment(['message' => 'Acesso negado. Permissao insuficiente.']);
});

test('viewer nao pode criar produto', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);
    $category = Category::factory()->create();

    $this->actingAs($viewer)
        ->postJson('/api/products', [
            'name' => 'Tentativa Viewer',
            'sku' => 'SKU-RBAC-004',
            'category_id' => $category->id,
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'stock_quantity' => 10,
        ])
        ->assertStatus(403);
});

// ==========================================
// Produtos: permissoes de exclusao
// ==========================================

test('admin pode deletar produto', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create();

    $this->actingAs($admin)
        ->deleteJson("/api/products/{$product->id}")
        ->assertNoContent();
});

test('manager nao pode deletar produto', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::factory()->create();

    $this->actingAs($manager)
        ->deleteJson("/api/products/{$product->id}")
        ->assertStatus(403);
});

// ==========================================
// Produtos: toggle e bulk (admin e manager)
// ==========================================

test('admin pode alternar status de produto', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patchJson("/api/products/{$product->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('is_active', false);
});

test('seller nao pode alternar status de produto', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $product = Product::factory()->create();

    $this->actingAs($seller)
        ->patchJson("/api/products/{$product->id}/toggle-active")
        ->assertStatus(403);
});

test('viewer nao pode excluir produtos em lote', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);
    $product = Product::factory()->create();

    $this->actingAs($viewer)
        ->postJson('/api/products/bulk-delete', ['ids' => [$product->id]])
        ->assertStatus(403);
});

// ==========================================
// Vendas: permissoes de criacao
// ==========================================

test('seller pode criar venda', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $product = Product::factory()->create([
        'stock_quantity' => 10,
        'sale_price' => 15.00,
        'is_active' => true,
    ]);

    $this->actingAs($seller)
        ->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])
        ->assertCreated();
});

test('viewer nao pode criar venda', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);
    $product = Product::factory()->create([
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $this->actingAs($viewer)
        ->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])
        ->assertStatus(403);
});

// ==========================================
// Clientes: permissoes de gerenciamento
// ==========================================

test('seller nao pode criar cliente', function () {
    $seller = User::factory()->create(['role' => 'seller']);

    $this->actingAs($seller)
        ->postJson('/api/customers', ['name' => 'Cliente Teste'])
        ->assertStatus(403);
});

test('manager pode criar cliente', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)
        ->postJson('/api/customers', ['name' => 'Cliente Manager'])
        ->assertCreated();
});

// ==========================================
// Leitura: todos os perfis autenticados podem ler
// ==========================================

test('viewer pode listar produtos', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);
    Product::factory()->count(3)->create();

    $this->actingAs($viewer)
        ->getJson('/api/products')
        ->assertOk();
});

test('viewer pode listar vendas', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);

    $this->actingAs($viewer)
        ->getJson('/api/sales')
        ->assertOk();
});

test('viewer pode acessar dashboard', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);

    $this->actingAs($viewer)
        ->getJson('/api/dashboard/summary')
        ->assertOk();
});
