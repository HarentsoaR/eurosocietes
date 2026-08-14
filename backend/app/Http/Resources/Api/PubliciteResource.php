<?php

namespace App\Http\Resources\Api;

class PubliciteResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'titre' => $this->titre,
            'description' => $this->description,
            'emplacement' => $this->emplacement,
            'url_cible' => $this->url_cible,
            'image_path' => $this->image_path,
            'statut' => $this->statut,
            'date_debut' => optional($this->date_debut)?->format('Y-m-d'),
            'date_fin' => optional($this->date_fin)?->format('Y-m-d'),
        ];
    }
}
