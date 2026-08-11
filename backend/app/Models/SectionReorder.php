<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A per-entity override of a canonical fiche section: reorder (position) and
 * toggle (visible). Morph columns mirror the existing Faq/ContenuIa/Document
 * convention (entity_type / entity_id).
 */
class SectionReorder extends Model
{
    protected $fillable = [
        'entity_type', 'entity_id', 'section_id', 'position', 'visible',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'visible' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function entitable(): MorphTo
    {
        return $this->morphTo('entitable', 'entity_type', 'entity_id');
    }
}
