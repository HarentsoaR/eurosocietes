<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ville extends Model
{
    use HasFactory;

    protected $fillable = ['code_insee', 'libelle', 'slug', 'departement_id', 'arrondissement', 'population'];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function codePostaux(): HasMany
    {
        return $this->hasMany(VilleCodePostal::class, 'ville_id');
    }
}
