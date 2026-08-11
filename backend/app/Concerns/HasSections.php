<?php

namespace App\Concerns;

use App\Models\Section;
use App\Models\SectionReorder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Adds fiche-block overrides to any entity (entreprise, ville, ...): a Section
 * is the canonical block; a SectionReorder row overrides its visibility and
 * position for a specific entity.
 *
 * Note: attribute values are read through getAttribute()/getRawOriginal()
 * rather than magic property access. A column named `visible` collides with
 * Eloquent's own Model::$visible (a protected array) and magic access inside a
 * closure bound to another Model instance returns that property, not the
 * database value.
 */
trait HasSections
{
    public function sectionOverrides(): MorphMany
    {
        return $this->morphMany(SectionReorder::class, 'entitable', 'entity_type', 'entity_id');
    }

    /**
     * Effective blocks for this entity: canonical sections merged with the
     * entity's overrides, ordered by resolved position. Returned as a
     * collection of ["section" => Section, "visible" => bool, "position" => int].
     */
    public function sections(): Collection
    {
        $overrides = $this->sectionOverrides()
            ->with('section')
            ->get()
            ->keyBy('section_id');

        return Section::orderBy('ordre')
            ->get()
            ->map(function (Section $section) use ($overrides): array {
                $override = $overrides->get($section->getKey());
                $overrideFound = $override instanceof SectionReorder;

                return [
                    'section' => $section,
                    'visible' => (bool) ($overrideFound
                        ? $override->getAttribute('visible')
                        : $section->getAttribute('visible')),
                    'position' => (int) ($overrideFound
                        ? $override->getAttribute('position')
                        : $section->getAttribute('ordre')),
                ];
            })
            ->sortBy('position')
            ->values();
    }
}
