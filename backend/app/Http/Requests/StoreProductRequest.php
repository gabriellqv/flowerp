<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de criacao de um novo produto.
 *
 * As regras garantem que dados obrigatorios estejam presentes,
 * que valores monetarios sejam coerentes e que o SKU seja unico
 * em toda a tabela de produtos.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Autorizacao delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validacao para criacao de produto.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:200'],
            'sku' => ['required', 'string', 'min:3', 'max:50', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'cost_price' => ['required', 'numeric', 'min:0.01'],
            'sale_price' => ['required', 'numeric', 'gt:cost_price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
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
            'sku.unique' => 'Este SKU já está cadastrado.',
            'sale_price.gt' => 'O preço de venda deve ser maior que o preço de custo.',
            'category_id.exists' => 'Categoria não encontrada.',
        ];
    }

    /**
     * Traducao dos nomes dos atributos para as mensagens padrão.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do produto',
            'sku' => 'SKU',
            'description' => 'descrição',
            'category_id' => 'categoria',
            'cost_price' => 'preço de custo',
            'sale_price' => 'preço de venda',
            'stock_quantity' => 'quantidade em estoque',
            'min_stock' => 'estoque mínimo',
        ];
    }
}
