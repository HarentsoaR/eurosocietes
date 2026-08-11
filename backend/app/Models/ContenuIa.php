<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContenuIa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contenus_ia';

    protected $fillable = ['entity_type', 'entity_id', 'type_contenu', 'contenu', 'statut', 'modele', 'prompt_version', 'generated_at'];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }
}
