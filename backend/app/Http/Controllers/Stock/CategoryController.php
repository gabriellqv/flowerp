<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::orderBy('name')->get());
    }

    /**
     * Exibe uma categoria especifica.
     *
     * @param  Category  $category  Categoria solicitada
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    /**
     * Cria uma nova categoria.
     *
     * @param  StoreCategoryRequest  $request  Dados validados da categoria
     * @return JsonResponse Categoria criada (201)
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json(new CategoryResource($category), 201);
    }

    /**
     * Atualiza uma categoria existente.
     *
     * @param  UpdateCategoryRequest  $request  Dados validados da categoria
     * @param  Category  $category  Categoria a ser atualizada
     * @return JsonResponse Categoria atualizada
     */
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
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
