<?php

namespace App\Http\Resources\Api;

class DepartementResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'slug' => $this->slug,
            'region_id' => $this->region_id,
        ];
    }
}
