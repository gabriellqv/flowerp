<?php

/*
|--------------------------------------------------------------------------
| Testes unitarios do CustomerService.
|
| Garante que a logica de negocio do servico de clientes
| funcione isoladamente, incluindo listagem com busca,
| criacao, atualizacao, toggle de status e desativacao em lote.
|--------------------------------------------------------------------------
*/

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->customerService = new CustomerService;
});

test('listCustomers retorna apenas clientes ativos com paginacao padrao', function () {
    Customer::factory()->count(5)->create(['is_active' => true]);
    Customer::factory()->count(3)->create(['is_active' => false]);

    $result = $this->customerService->listCustomers();

    expect($result->count())->toBe(5);
    expect($result->first()->is_active)->toBeTrue();
});

test('listCustomers busca por nome, e-mail e documento', function () {
    Customer::factory()->create(['name' => 'Joao Silva', 'is_active' => true]);
    Customer::factory()->create(['email' => 'maria@teste.com', 'is_active' => true]);
    Customer::factory()->create(['document' => '123.456.789-00', 'is_active' => true]);
    Customer::factory()->create(['name' => 'Carlos Souza', 'is_active' => true]);

    $result = $this->customerService->listCustomers('Joao');

    expect($result->total())->toBe(1);
    expect($result->first()->name)->toBe('Joao Silva');
});

test('listCustomers respeita parametro perPage', function () {
    Customer::factory()->count(30)->create(['is_active' => true]);

    $result = $this->customerService->listCustomers(null, 10);

    expect($result->perPage())->toBe(10);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(30);
});

test('createCustomer cria um novo cliente com os dados fornecidos', function () {
    $data = [
        'name' => 'Novo Cliente',
        'email' => 'cliente@teste.com',
        'phone' => '(11) 99999-9999',
        'document' => '111.222.333-44',
    ];

    $customer = $this->customerService->createCustomer($data);

    expect($customer->id)->not->toBeNull();
    expect($customer->name)->toBe('Novo Cliente');
    expect($customer->email)->toBe('cliente@teste.com');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Novo Cliente',
    ]);
});

test('updateCustomer atualiza os campos de um cliente', function () {
    $customer = Customer::factory()->create([
        'name' => 'Antigo',
        'email' => 'antigo@teste.com',
    ]);

    $updated = $this->customerService->updateCustomer($customer, [
        'name' => 'Atualizado',
        'phone' => '(21) 88888-8888',
    ]);

    expect($updated->name)->toBe('Atualizado');
    expect($updated->phone)->toBe('(21) 88888-8888');
    expect($updated->email)->toBe('antigo@teste.com');
});

test('toggleCustomerActive alterna status ativo para inativo', function () {
    $customer = Customer::factory()->create(['is_active' => true]);

    $toggled = $this->customerService->toggleCustomerActive($customer);
    expect($toggled->is_active)->toBeFalse();

    $toggledAgain = $this->customerService->toggleCustomerActive($toggled);
    expect($toggledAgain->is_active)->toBeTrue();
});

test('bulkDeactivateCustomers desativa multiplos clientes em lote', function () {
    $customer1 = Customer::factory()->create(['is_active' => true]);
    $customer2 = Customer::factory()->create(['is_active' => true]);
    $customer3 = Customer::factory()->create(['is_active' => true]);

    $this->customerService->bulkDeactivateCustomers([
        $customer1->id,
        $customer2->id,
    ]);

    expect(Customer::find($customer1->id)->is_active)->toBeFalse();
    expect(Customer::find($customer2->id)->is_active)->toBeFalse();
    expect(Customer::find($customer3->id)->is_active)->toBeTrue();
});
