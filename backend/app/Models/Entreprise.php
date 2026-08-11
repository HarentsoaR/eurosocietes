<?php

namespace App\Models;

use App\Concerns\HasSections;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use HasFactory, HasSections, SoftDeletes;

    protected $fillable = [
        'siren', 'denomination', 'nom', 'prenoms', 'sigle', 'enseigne',
        'categorie_juridique', 'categorie_entreprise', 'tranche_effectifs',
        'annee_effectifs', 'caractere_employeur', 'etat_administratif',
        'statut_diffusion', 'date_creation', 'date_radiation', 'date_debut_activite',
        'activite_naf_id', 'ville_id', 'adresse_complete', 'slug',
        'allow_public_contacts', 'visible',
    ];

    protected function casts(): array
    {
        return [
            'annee_effectifs' => 'integer',
            'caractere_employeur' => 'boolean',
            'allow_public_contacts' => 'boolean',
            'visible' => 'boolean',
            'date_creation' => 'date',
            'date_radiation' => 'date',
            'date_debut_activite' => 'date',
        ];
    }

    public function activiteNaf(): BelongsTo
    {
        return $this->belongsTo(ActiviteNaf::class, 'activite_naf_id');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function specialites(): BelongsToMany
    {
        return $this->belongsToMany(Specialite::class, 'entreprise_specialite');
    }

    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class);
    }

    public function dirigeants(): HasMany
    {
        return $this->hasMany(Dirigeant::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'entitable', 'entity_type', 'entity_id');
    }

    public function contenusIa(): MorphMany
    {
        return $this->morphMany(ContenuIa::class, 'entitable', 'entity_type', 'entity_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'entitable', 'entity_type', 'entity_id');
    }

    public function passeport(): HasOne
    {
        return $this->hasOne(Passeport::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function publicites(): HasMany
    {
        return $this->hasMany(Publicite::class);
    }

    public function scopeRecherche(Builder $query, string $terme): Builder
    {
        return $query->whereRaw('search_vector @@ plainto_tsquery(\'french\', ?)', [$terme]);
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('etat_administratif', 'A');
    }
}
