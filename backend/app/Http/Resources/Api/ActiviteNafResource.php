<?php

namespace App\Http\Resources\Api;

class ActiviteNafResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'section' => $this->section,
            'section_libelle' => $this->section_libelle,
            'division' => $this->division,
            'division_libelle' => $this->division_libelle,
            'groupe' => $this->groupe,
            'groupe_libelle' => $this->groupe_libelle,
            'classe' => $this->classe,
            'classe_libelle' => $this->classe_libelle,
            'libelle' => $this->libelle,
        ];
    }
}
