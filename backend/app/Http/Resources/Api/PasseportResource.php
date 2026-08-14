<?php

namespace App\Http\Resources\Api;

class PasseportResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'statut' => $this->statut,
            'score_confidence' => $this->score_confidence,
            'badges' => $this->badges,
            'is_validated' => (bool) $this->is_validated,
            'validated_at' => optional($this->validated_at)?->toISOString(),
        ];
    }
}
