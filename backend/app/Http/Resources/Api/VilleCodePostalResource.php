<?php

namespace App\Http\Resources\Api;

class VilleCodePostalResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ville_id' => $this->ville_id,
            'code_postal' => $this->code_postal,
        ];
    }
}
