<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de atualização de uma categoria existente.
 *
 * Ignora o próprio registro na validação de unicidade do nome.
 */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * Autorização delegada ao middleware de rota (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização de categoria.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => ['required', 'string', 'max:100', "unique:categories,name,{$categoryId}"],
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
