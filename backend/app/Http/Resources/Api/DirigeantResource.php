<?php

namespace App\Http\Resources\Api;

class DirigeantResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'qualite' => $this->qualite,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
            'date_debut_fonction' => optional($this->date_debut_fonction)?->format('Y-m-d'),
            'est_principal' => (bool) $this->est_principal,
        ];
    }
}
