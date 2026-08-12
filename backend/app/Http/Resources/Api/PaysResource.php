<?php

namespace App\Http\Resources\Api;

class PaysResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code_iso2' => $this->code_iso2,
            'code_iso3' => $this->code_iso3,
            'code_insee' => $this->code_insee,
            'libelle' => $this->libelle,
            'slug' => $this->slug,
        ];
    }
}
