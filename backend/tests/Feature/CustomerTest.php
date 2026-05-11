<?php

/*
|--------------------------------------------------------------------------
| Testes de CRUD de clientes.
|
| Valida criacao, atualizacao, consulta individual, busca,
| unicidade de documento (CPF), toggle de status ativo
| e exclusao em lote (soft delete via is_active = false).
|--------------------------------------------------------------------------
*/

use App\Models\Customer;
use App\Models\User;

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => 'manager']);
});

// ==========================================
// Criacao
// ==========================================

test('cria cliente com todos os campos', function () {
    $this->actingAs($this->manager)
        ->postJson('/api/customers', [
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.com',
            'phone' => '(38) 99999-0000',
            'document' => '123.456.789-00',
        ])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Maria Silva'])
        ->assertJsonFragment(['document' => '123.456.789-00']);
});

test('cria cliente com apenas o nome (campos opcionais)', function () {
    $this->actingAs($this->manager)
        ->postJson('/api/customers', ['name' => 'Cliente Anonimo'])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Cliente Anonimo']);
});

test('rejeita cliente sem nome', function () {
    $this->actingAs($this->manager)
        ->postJson('/api/customers', ['email' => 'sem@nome.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('rejeita cliente com documento duplicado', function () {
    Customer::factory()->create(['document' => '999.888.777-66']);

    $this->actingAs($this->manager)
        ->postJson('/api/customers', [
            'name' => 'Duplicado',
            'document' => '999.888.777-66',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('document');
});

test('rejeita cliente com email invalido', function () {
    $this->actingAs($this->manager)
        ->postJson('/api/customers', [
            'name' => 'Email Ruim',
            'email' => 'nao-e-email',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

// ==========================================
// Atualizacao
// ==========================================

test('atualiza dados do cliente', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($this->manager)
        ->putJson("/api/customers/{$customer->id}", [
            'name' => 'Nome Atualizado',
            'phone' => '(11) 98765-4321',
        ])
        ->assertOk()
        ->assertJsonFragment(['name' => 'Nome Atualizado']);
});

test('permite atualizar cliente mantendo o mesmo documento', function () {
    $customer = Customer::factory()->create(['document' => '111.222.333-44']);

    $this->actingAs($this->manager)
        ->putJson("/api/customers/{$customer->id}", [
            'name' => 'Mesmo Doc',
            'document' => '111.222.333-44',
        ])
        ->assertOk();
});

test('rejeita atualizar com documento de outro cliente', function () {
    Customer::factory()->create(['document' => '555.666.777-88']);
    $customer = Customer::factory()->create(['document' => '111.222.333-44']);

    $this->actingAs($this->manager)
        ->putJson("/api/customers/{$customer->id}", [
            'document' => '555.666.777-88',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('document');
});

// ==========================================
// Consulta e busca
// ==========================================

test('listagem retorna somente clientes ativos', function () {
    Customer::factory()->create(['is_active' => true]);
    Customer::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->manager)
        ->getJson('/api/customers');

    $response->assertOk();
    $data = $response->json('data');
    expect(count($data))->toBe(1);
});

test('busca por nome funciona', function () {
    Customer::factory()->create(['name' => 'Carlos Alberto']);
    Customer::factory()->create(['name' => 'Fernanda Lima']);

    $response = $this->actingAs($this->manager)
        ->getJson('/api/customers?search=Carlos');

    $data = $response->json('data');
    expect(count($data))->toBe(1);
    expect($data[0]['name'])->toBe('Carlos Alberto');
});

test('show retorna dados do cliente', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($this->manager)
        ->getJson("/api/customers/{$customer->id}")
        ->assertOk()
        ->assertJsonFragment(['id' => $customer->id]);
});

// ==========================================
// Toggle e bulk
// ==========================================

test('toggle alterna is_active do cliente', function () {
    $customer = Customer::factory()->create(['is_active' => true]);

    $this->actingAs($this->manager)
        ->patchJson("/api/customers/{$customer->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('is_active', false);
});

test('exclusao em lote desativa multiplos clientes', function () {
    $customers = Customer::factory()->count(3)->create(['is_active' => true]);
    $ids = $customers->pluck('id')->toArray();

    $this->actingAs($this->manager)
        ->postJson('/api/customers/bulk-delete', ['ids' => $ids])
        ->assertNoContent();

    foreach ($ids as $id) {
        expect(Customer::find($id)->is_active)->toBeFalse();
    }
});
