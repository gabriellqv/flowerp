<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de atualização de um cliente existente.
 *
 * Permite atualização parcial (sometimes) e ignora o próprio
 * registro na validação de unicidade do documento.
 */
class UpdateCustomerRequest extends FormRequest
{
    /**
     * Autorização delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização de cliente.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20', "unique:customers,document,{$customerId}"],
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
