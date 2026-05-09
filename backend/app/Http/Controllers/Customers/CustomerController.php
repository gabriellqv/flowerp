<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de gerenciamento de clientes.
 *
 * Fornece listagem com busca, criacao e consulta individual
 * de clientes ativos no sistema.
 */
class CustomerController extends Controller
{
    /**
     * Lista clientes ativos com busca e paginacao.
     *
     * @param  Request  $request  Query params: search, page
     */
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
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
     * Exibe detalhes de um cliente especifico.
     *
     * @param  Customer  $customer  Cliente resolvido via Route Model Binding
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }
}
