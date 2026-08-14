<?php

namespace App\Http\Resources\Api;

class FaqResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'question' => $this->question,
            'reponse' => $this->reponse,
            'ordre' => $this->ordre,
            'visible' => (bool) $this->getRawOriginal('visible'),
        ];
    }
}
