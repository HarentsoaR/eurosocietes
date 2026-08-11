<?php

namespace App\Import;

use App\Models\Departement;
use App\Models\Ville;
use Illuminate\Support\Facades\DB;

class CogImporter
{
    private CsvReader $reader;

    public function __construct(?CsvReader $reader = null)
    {
        $this->reader = $reader ?? new CsvReader();
    }

    /**
     * @return array{villes_inserees: int, villes_maj: int}
     */
    public function importer(string $cheminCommunes, ?string $cheminGeofla = null): array
    {
        $geo = $this->lireGeofla($cheminGeofla);

        $stats = ['villes_inserees' => 0, 'villes_maj' => 0];
        $codesExistants = Ville::pluck('id', 'code_insee')->all();

        foreach ($this->reader->lireLots($cheminCommunes, 2000) as $lot) {
            DB::transaction(function () use ($lot, $geo, &$stats, &$codesExistants) {
                foreach ($lot as $ligne) {
                    $codeInsee = str_pad(trim($ligne['code_insee']), 5, '0', STR_PAD_LEFT);
                    $departement = Departement::where('code', trim($ligne['code_departement']))->first();
                    if ($departement === null) {
                        continue;
                    }

                    $ville = Ville::where('code_insee', $codeInsee)->first();
                    if ($ville === null) {
                        $ville = Ville::create([
                            'code_insee' => $codeInsee,
                            'libelle' => trim($ligne['libelle']),
                            'slug' => $this->slugifier(trim($ligne['libelle']), $codeInsee),
                            'departement_id' => $departement->id,
                        ]);
                        $codesExistants[$codeInsee] = $ville->id;
                        $stats['villes_inserees']++;
                    } else {
                        $stats['villes_maj']++;
                    }

                    $codePostal = trim($ligne['code_postal']);
                    if (! $ville->codePostaux()->where('code_postal', $codePostal)->exists()) {
                        $ville->codePostaux()->create(['code_postal' => $codePostal]);
                    }

                    if (isset($geo[$codeInsee])) {
                        [$lat, $lng] = $geo[$codeInsee];
                        DB::statement(
                            'UPDATE villes SET latlng = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
                            [$lng, $lat, $ville->id]
                        );
                    }
                }
            });
        }

        return $stats;
    }

    /**
     * @return array<string, array{0: float, 1: float}>
     */
    private function lireGeofla(?string $chemin): array
    {
        if ($chemin === null) {
            return [];
        }

        $geo = [];
        foreach ($this->reader->lireLots($chemin, 2000) as $lot) {
            foreach ($lot as $ligne) {
                $geo[str_pad(trim($ligne['code_insee']), 5, '0', STR_PAD_LEFT)] = [
                    (float) str_replace(',', '.', $ligne['latitude']),
                    (float) str_replace(',', '.', $ligne['longitude']),
                ];
            }
        }

        return $geo;
    }

    private function slugifier(string $libelle, string $suffixe): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $libelle), '-'));

        return $slug !== '' ? $slug.'-'.$suffixe : $suffixe;
    }
}
