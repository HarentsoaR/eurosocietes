<?php

namespace App\Http\Resources\Api;

class SectionResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'section' => [
                'id' => $this['section']->id,
                'code' => $this['section']->code,
                'libelle' => $this['section']->libelle,
                'type' => $this['section']->type,
                'ordre' => $this['section']->ordre,
            ],
            'visible' => (bool) $this['visible'],
            'position' => (int) $this['position'],
        ];
    }
}
