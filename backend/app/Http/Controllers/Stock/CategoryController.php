<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de categorias de produtos.
 *
 * Fornece CRUD completo de categorias
 * com validacao de unicidade do nome.
 */
class CategoryController extends Controller
{
    /**
     * Lista todas as categorias em ordem alfabetica.
     */
    public function index(): JsonResponse
    {
        return response()->json(Category::orderBy('name')->get());
    }

    /**
     * Exibe uma categoria especifica.
     *
     * @param  Category  $category  Categoria solicitada
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    /**
     * Cria uma nova categoria.
     *
     * @param  Request  $request  Dados da categoria (name)
     * @return JsonResponse Categoria criada (201)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories']]);

        $category = Category::create($request->only('name'));

        return response()->json($category, 201);
    }

    /**
     * Atualiza uma categoria existente.
     *
     * @param  Request  $request  Dados da categoria (name)
     * @param  Category  $category  Categoria a ser atualizada
     * @return JsonResponse Categoria atualizada
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
        ]);

        $category->update($request->only('name'));

        return response()->json($category);
    }

    /**
     * Remove uma categoria sem produtos vinculados.
     *
     * @param  Category  $category  Categoria a ser removida
     * @return JsonResponse Resposta vazia (204) ou erro (422)
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Nao e possivel excluir: existem produtos vinculados a esta categoria.',
            ], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
