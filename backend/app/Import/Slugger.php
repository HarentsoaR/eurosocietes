<?php

namespace App\Import;

use Illuminate\Support\Str;

class Slugger
{
    /**
     * Génère un slug unique en suffixant par un code stable (SIREN, SIRET, code INSEE).
     */
    public static function faire(string $libelle, string $suffixe): string
    {
        $slug = Str::slug($libelle);

        return $slug !== '' ? $slug.'-'.$suffixe : $suffixe;
    }
}
