<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'source', 'fichier', 'statut', 'lignes_total', 'lignes_traitees',
        'lignes_inserees', 'lignes_maj', 'lignes_radiees', 'lignes_erreur',
        'resume_state', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'lignes_total' => 'integer',
            'lignes_traitees' => 'integer',
            'lignes_inserees' => 'integer',
            'lignes_maj' => 'integer',
            'lignes_radiees' => 'integer',
            'lignes_erreur' => 'integer',
            'resume_state' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ImportLog::class, 'import_id');
    }
}
