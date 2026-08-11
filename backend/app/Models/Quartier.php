<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quartier extends Model
{
    use HasFactory;

    protected $fillable = ['ville_id', 'libelle', 'slug', 'description'];

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }
}
