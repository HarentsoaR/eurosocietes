<?php

namespace App\Http\Resources\Api;

class SpecialiteResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
