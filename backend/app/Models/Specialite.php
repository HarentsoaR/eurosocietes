<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialite extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'slug', 'description'];

    public function entreprises(): BelongsToMany
    {
        return $this->belongsToMany(Entreprise::class, 'entreprise_specialite');
    }
}
