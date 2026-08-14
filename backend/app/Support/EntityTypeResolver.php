<?php

namespace App\Support;

use App\Models\Entreprise;
use App\Models\Etablissement;
use App\Models\Quartier;
use App\Models\Ville;

class EntityTypeResolver
{
    private const ALLOWED = [
        'entreprise' => Entreprise::class,
        'etablissement' => Etablissement::class,
        'ville' => Ville::class,
        'quartier' => Quartier::class,
    ];

    public static function resolve(string $type): string
    {
        $fqcn = self::ALLOWED[$type] ?? null;

        if ($fqcn === null) {
            throw new \InvalidArgumentException("Type d'entité non autorisé.");
        }

        return $fqcn;
    }

    public static function allowed(): array
    {
        return self::ALLOWED;
    }
}
