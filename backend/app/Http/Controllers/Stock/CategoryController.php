<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de categorias de produtos.
 *
 * Fornece listagem e criacao de categorias,
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
}
