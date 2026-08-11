<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['entity_type', 'entity_id', 'type', 'titre', 'chemin', 'mime_type', 'taille', 'statut_validation'];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
        ];
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }
}
