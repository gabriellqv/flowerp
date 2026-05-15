<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller de gerenciamento de clientes.
 *
 * Fornece listagem com busca, criação, edição, consulta individual,
 * alternância de status e exclusão em lote de clientes no sistema.
 */
class CustomerController extends Controller
{
    /**
     * Lista clientes ativos com busca e paginação.
     *
     * @param  Request  $request  Query params: search, page
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('document', 'like', "%{$request->search}%"))
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        return CustomerResource::collection($customers);
    }

    /**
     * Cadastra um novo cliente.
     *
     * @param  StoreCustomerRequest  $request  Dados validados do cliente
     * @return JsonResponse Cliente criado (201)
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json(new CustomerResource($customer), 201);
    }

    /**
     * Exibe detalhes de um cliente específico.
     *
     * @param  Customer  $customer  Cliente resolvido via Route Model Binding
     */
    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    /**
     * Atualiza os dados de um cliente existente.
     *
     * @param  UpdateCustomerRequest  $request  Dados validados a atualizar
     * @param  Customer  $customer  Cliente resolvido via Route Model Binding
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Alterna o status ativo/inativo de um cliente.
     *
     * @param  Customer  $customer  Cliente a ser alternado
     * @return JsonResponse Cliente com novo estado
     */
    public function toggleActive(Customer $customer): CustomerResource
    {
        $customer->update(['is_active' => ! $customer->is_active]);

        return new CustomerResource($customer);
    }

    /**
     * Desativa múltiplos clientes em lote.
     *
     * @param  Request  $request  Deve conter array 'ids' com UUIDs
     * @return JsonResponse 204 No Content
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'uuid'],
        ]);

        Customer::whereIn('id', $request->input('ids'))->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
