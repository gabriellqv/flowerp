<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Serviço de gerenciamento de clientes.
 *
 * Centraliza a lógica de negócio relacionada à criação,
 * atualização, listagem e inativação de clientes.
 */
class CustomerService
{
    /**
     * Lista clientes ativos com busca e paginação.
     *
     * @param  string|null  $search  Termo de busca (nome, e-mail ou documento)
     * @param  int  $perPage  Quantidade de itens por página
     * @return LengthAwarePaginator<Customer>
     */
    public function listCustomers(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        return Customer::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('document', 'like', "%{$search}%"))
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Cria um novo cliente com os dados validados.
     *
     * @param  array{
     *   name: string,
     *   email?: string|null,
     *   phone?: string|null,
     *   document?: string|null
     * }  $data  Dados do cliente
     * @return Customer Cliente recém-criado
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Atualiza os dados de um cliente existente.
     *
     * @param  Customer  $customer  Instância do cliente a ser atualizado
     * @param  array{
     *   name?: string,
     *   email?: string|null,
     *   phone?: string|null,
     *   document?: string|null
     * }  $data  Dados parciais a atualizar
     * @return Customer Cliente atualizado
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    /**
     * Alterna o status ativo/inativo de um cliente.
     *
     * @param  Customer  $customer  Cliente a ter o estado alternado
     * @return Customer Cliente com o novo estado
     */
    public function toggleCustomerActive(Customer $customer): Customer
    {
        $customer->update(['is_active' => ! $customer->is_active]);

        return $customer;
    }

    /**
     * Desativa múltiplos clientes em lote.
     *
     * @param  array<int, string>  $ids  Array de UUIDs dos clientes
     */
    public function bulkDeactivateCustomers(array $ids): void
    {
        Customer::whereIn('id', $ids)->update(['is_active' => false]);
    }
}
