<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de API para padronização da resposta de Venda.
 */
class SaleResource extends JsonResource
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
            'seller_id' => $this->seller_id,
            'seller' => [
                'id' => $this->seller?->id,
                'name' => $this->seller?->name,
            ],
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            'total_amount' => (float) $this->total_amount,
            'discount' => (float) $this->discount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
