<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de cadastro de um novo cliente.
 *
 * Garante que o nome seja obrigatório, e-mail válido
 * e documento (CPF/CNPJ) único na base.
 */
class StoreCustomerRequest extends FormRequest
{
    /**
     * Autorização delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de cliente.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20', 'unique:customers'],
        ];
    }

    /**
     * Mensagens personalizadas para regras específicas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.unique' => 'Este documento já está cadastrado.',
        ];
    }
}
