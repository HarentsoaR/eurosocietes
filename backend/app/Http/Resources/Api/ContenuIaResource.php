<?php

namespace App\Http\Resources\Api;

class ContenuIaResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'type_contenu' => $this->type_contenu,
            'contenu' => $this->contenu,
            'statut' => $this->statut,
            'modele' => $this->modele,
            'generated_at' => optional($this->generated_at)?->toISOString(),
        ];
    }
}
