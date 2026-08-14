<?php

namespace App\Http\Resources\Api;

class StatistiqueResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'periode' => $this->periode,
            'compteur' => $this->compteur,
        ];
    }
}
