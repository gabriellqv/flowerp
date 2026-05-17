<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de criação de uma nova categoria.
 *
 * Garante unicidade do nome na tabela de categorias.
 */
class StoreCategoryRequest extends FormRequest
{
    /**
     * Autorização delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de categoria.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:categories'],
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
            'name.unique' => 'Esta categoria já existe.',
        ];
    }
}
