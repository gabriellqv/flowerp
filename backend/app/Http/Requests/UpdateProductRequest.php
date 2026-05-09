<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida os dados de atualizacao de um produto existente.
 *
 * Diferente do Store, todas as regras usam `sometimes`,
 * permitindo PATCH parcial. O SKU ignora o proprio produto
 * na verificacao de unicidade.
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Autorizacao delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validacao para atualizacao de produto.
     *
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:200'],
            'sku' => ['sometimes', 'string', 'min:3', 'max:50',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'description' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'uuid', 'exists:categories,id'],
            'cost_price' => ['sometimes', 'numeric', 'min:0.01'],
            'sale_price' => ['sometimes', 'numeric', 'gt:cost_price'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Mensagens personalizadas para regras especificas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.unique' => 'Este SKU ja esta cadastrado.',
            'sale_price.gt' => 'Preco de venda deve ser maior que o preco de custo.',
            'category_id.exists' => 'Categoria nao encontrada.',
        ];
    }
}
