<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pays extends Model
{
    use HasFactory;

    protected $fillable = ['code_iso2', 'code_iso3', 'code_insee', 'libelle', 'slug'];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
