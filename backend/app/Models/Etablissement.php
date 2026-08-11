<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etablissement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'siret', 'nic', 'entreprise_id', 'est_siege', 'activite_naf_id',
        'etat_administratif', 'statut_diffusion', 'date_creation', 'date_debut',
        'numero_voie', 'indice_repetition', 'type_voie', 'libelle_voie',
        'complement_adresse', 'code_postal', 'ville_id', 'libelle_commune',
        'adresse_complete', 'slug',
    ];

    protected function casts(): array
    {
        return [
            'est_siege' => 'boolean',
            'date_creation' => 'date',
            'date_debut' => 'date',
        ];
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }
}
