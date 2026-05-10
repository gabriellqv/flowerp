<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('document', 'like', "%{$request->search}%"))
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        return response()->json($customers);
    }

    /**
     * Cadastra um novo cliente.
     *
     * @param  Request  $request  Dados do cliente (name, email, phone, document)
     * @return JsonResponse Cliente criado (201)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20', 'unique:customers'],
        ]);

        $customer = Customer::create($request->only(['name', 'email', 'phone', 'document']));

        return response()->json($customer, 201);
    }

    /**
     * Exibe detalhes de um cliente específico.
     *
     * @param  Customer  $customer  Cliente resolvido via Route Model Binding
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    /**
     * Atualiza os dados de um cliente existente.
     *
     * @param  Request  $request  Dados parciais a atualizar
     * @param  Customer  $customer  Cliente resolvido via Route Model Binding
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20', "unique:customers,document,{$customer->id}"],
        ]);

        $customer->update($request->only(['name', 'email', 'phone', 'document']));

        return response()->json($customer);
    }

    /**
     * Alterna o status ativo/inativo de um cliente.
     *
     * @param  Customer  $customer  Cliente a ser alternado
     * @return JsonResponse Cliente com novo estado
     */
    public function toggleActive(Customer $customer): JsonResponse
    {
        $customer->update(['is_active' => ! $customer->is_active]);

        return response()->json($customer);
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
