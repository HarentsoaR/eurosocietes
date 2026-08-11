<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Canonical fiche blocks. Per-entity visibility/ordering overrides live in
 * section_reorders (morph) so an entity can hide or move a block without
 * touching the global layout.
 */
class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'libelle', 'type', 'ordre', 'visible',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'visible' => 'boolean',
        ];
    }

    public function reorders(): HasMany
    {
        return $this->hasMany(SectionReorder::class);
    }
}
