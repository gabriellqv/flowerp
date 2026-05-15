<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados para execução de uma nova venda.
 *
 * Garante que ao menos um item seja enviado, que os produtos
 * existam na base e que as quantidades sejam positivas.
 */
class StoreSaleRequest extends FormRequest
{
    /**
     * Autorização delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de venda.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string'],
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
            'items.required' => 'A venda deve conter ao menos um item.',
            'items.min' => 'A venda deve conter ao menos um item.',
            'items.*.product_id.exists' => 'Produto não encontrado.',
            'items.*.quantity.min' => 'A quantidade deve ser ao menos 1.',
            'customer_id.exists' => 'Cliente não encontrado.',
        ];
    }
}
