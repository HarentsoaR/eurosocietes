<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntrepriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(\App\Enums\Permission::CompanyCreate->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'siren' => ['required', 'string', 'size:9', 'unique:entreprises,siren'],
            'denomination' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'prenoms' => ['nullable', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:50'],
            'enseigne' => ['nullable', 'string', 'max:255'],
            'categorie_juridique' => ['nullable', 'string', 'max:10'],
            'categorie_entreprise' => ['nullable', 'string', 'max:10'],
            'tranche_effectifs' => ['nullable', 'string', 'max:10'],
            'annee_effectifs' => ['nullable', 'integer'],
            'caractere_employeur' => ['nullable', 'boolean'],
            'etat_administratif' => ['nullable', 'string', 'size:1'],
            'date_creation' => ['nullable', 'date'],
            'date_radiation' => ['nullable', 'date'],
            'activite_naf_id' => ['nullable', 'integer', 'exists:activites_naf,id'],
            'ville_id' => ['nullable', 'integer', 'exists:villes,id'],
            'adresse_complete' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:180'],
            'visible' => ['nullable', 'boolean'],
            'allow_public_contacts' => ['nullable', 'boolean'],
        ];
    }
}