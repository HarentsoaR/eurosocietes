<?php

namespace App\Http\Resources\Api;

class RegionResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'slug' => $this->slug,
            'pays_id' => $this->pays_id,
        ];
    }
}
