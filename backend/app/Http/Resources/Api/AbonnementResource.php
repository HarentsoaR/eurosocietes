<?php

namespace App\Http\Resources\Api;

class AbonnementResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'utilisateur_id' => $this->utilisateur_id,
            'plan' => $this->plan,
            'statut' => $this->statut,
            'stripe_id' => $this->stripe_id,
            'date_debut' => optional($this->date_debut)?->format('Y-m-d'),
            'date_fin' => optional($this->date_fin)?->format('Y-m-d'),
            'renouvellement_auto' => (bool) $this->renouvellement_auto,
        ];
    }
}
