<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Monument extends Model
{
    use HasFactory;

    protected $fillable = ['ville_id', 'quartier_id', 'libelle', 'slug', 'type', 'ref_merimee', 'description', 'adresse'];

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class);
    }
}
