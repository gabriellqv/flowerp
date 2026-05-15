<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de API para padronização da resposta de Item de Venda.
 */
class SaleItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'created_at' => $this->created_at,
        ];
    }
}
