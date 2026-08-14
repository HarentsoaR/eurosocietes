<?php

namespace App\Http\Resources\Api;

class DocumentResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'type' => $this->type,
            'titre' => $this->titre,
            'chemin' => $this->chemin,
            'mime_type' => $this->mime_type,
            'taille' => $this->taille,
            'statut_validation' => $this->statut_validation,
        ];
    }
}
