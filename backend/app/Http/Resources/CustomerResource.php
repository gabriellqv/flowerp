<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de API para padronização da resposta de Cliente.
 *
 * Transforma o model Customer em um array JSON estruturado,
 * garantindo consistência no contrato da API e ocultando
 * detalhes internos de implementação.
 */
class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
