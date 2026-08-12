<?php

namespace App\Http\Resources\Api;

class EntrepriseResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'siren' => $this->siren,
            'denomination' => $this->denomination,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
            'sigle' => $this->sigle,
            'enseigne' => $this->enseigne,
            'categorie_juridique' => $this->categorie_juridique,
            'categorie_entreprise' => $this->categorie_entreprise,
            'tranche_effectifs' => $this->tranche_effectifs,
            'annee_effectifs' => $this->annee_effectifs,
            'caractere_employeur' => (bool) $this->caractere_employeur,
            'etat_administratif' => $this->etat_administratif,
            'date_creation' => optional($this->date_creation)?->format('Y-m-d'),
            'date_radiation' => optional($this->date_radiation)?->format('Y-m-d'),
            'activite_naf_id' => $this->activite_naf_id,
            'ville_id' => $this->ville_id,
            'adresse_complete' => $this->adresse_complete,
            'slug' => $this->slug,
            'visible' => (bool) $this->getRawOriginal('visible'),
            'allow_public_contacts' => (bool) $this->allow_public_contacts,
            'etablissements' => \App\Http\Resources\Api\EtablissementResource::collection($this->whenLoaded('etablissements')),
            'dirigeants' => \App\Http\Resources\Api\DirigeantResource::collection($this->whenLoaded('dirigeants')),
        ];
    }
}