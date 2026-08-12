<?php

namespace App\Http\Resources\Api;

class VilleResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code_insee' => $this->code_insee,
            'libelle' => $this->libelle,
            'slug' => $this->slug,
            'departement_id' => $this->departement_id,
            'population' => $this->population,
        ];
    }
}
