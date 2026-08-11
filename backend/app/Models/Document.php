<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['entity_type', 'entity_id', 'type', 'titre', 'chemin', 'mime_type', 'taille', 'statut_validation'];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('fichiers')->singleFile();
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }
}
