<?php

namespace App\Http\Resources\Api;

class EtablissementResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'siret' => $this->siret,
            'nic' => $this->nic,
            'entreprise_id' => $this->entreprise_id,
            'est_siege' => (bool) $this->est_siege,
            'etat_administratif' => $this->etat_administratif,
            'adresse_complete' => $this->adresse_complete,
            'code_postal' => $this->code_postal,
            'libelle_commune' => $this->libelle_commune,
            'slug' => $this->slug,
        ];
    }
}
