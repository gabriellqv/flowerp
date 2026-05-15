<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de API para padronização da resposta de Produto.
 *
 * Transforma o model Product em um array JSON estruturado.
 */
class ProductResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'cost_price' => (float) $this->cost_price,
            'sale_price' => (float) $this->sale_price,
            'stock_quantity' => $this->stock_quantity,
            'min_stock' => $this->min_stock,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
