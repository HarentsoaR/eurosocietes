<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActiviteNaf extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activites_naf';

    protected $fillable = [
        'code', 'section', 'section_libelle', 'division', 'division_libelle',
        'groupe', 'groupe_libelle', 'classe', 'classe_libelle', 'libelle',
    ];

    public function entreprises(): HasMany
    {
        return $this->hasMany(Entreprise::class, 'activite_naf_id');
    }
}
