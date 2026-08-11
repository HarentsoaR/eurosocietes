<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dirigeant extends Model
{
    use HasFactory;

    protected $fillable = ['entreprise_id', 'qualite', 'nom', 'prenoms', 'date_debut_fonction', 'est_principal'];

    protected function casts(): array
    {
        return [
            'est_principal' => 'boolean',
            'date_debut_fonction' => 'date',
        ];
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }
}
