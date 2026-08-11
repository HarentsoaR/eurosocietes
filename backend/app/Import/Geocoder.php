<?php

namespace App\Import;

use App\Models\Entreprise;
use App\Models\Etablissement;
use Illuminate\Support\Facades\DB;

class Geocoder
{
    /**
     * Retourne [lat, lng] de la ville si géolocalisée.
     *
     * @return array{0: float, 1: float}|null
     */
    public function coordonneesCommune(int $villeId): ?array
    {
        $ligne = DB::table('villes')
            ->selectRaw('ST_Y(latlng::geometry) AS lat, ST_X(latlng::geometry) AS lng')
            ->where('id', $villeId)
            ->whereNotNull('latlng')
            ->first();

        if ($ligne === null) {
            return null;
        }

        return [(float) $ligne->lat, (float) $ligne->lng];
    }

    public function appliquerFallback(int $villeId, ?int $entrepriseId = null, ?int $etablissementId = null): void
    {
        $coordonnees = $this->coordonneesCommune($villeId);
        if ($coordonnees === null) {
            return;
        }

        $this->appliquerCoordonnees($coordonnees, $entrepriseId, $etablissementId);
    }

    /**
     * @param  array{0: float, 1: float}  $coordonnees
     */
    private function appliquerCoordonnees(array $coordonnees, ?int $entrepriseId = null, ?int $etablissementId = null): void
    {
        [$lat, $lng] = $coordonnees;

        if ($entrepriseId !== null) {
            DB::statement(
                'UPDATE entreprises SET latlng = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
                [$lng, $lat, $entrepriseId]
            );
        }

        if ($etablissementId !== null) {
            DB::statement(
                'UPDATE etablissements SET latlng = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
                [$lng, $lat, $etablissementId]
            );
        }
    }

    public function appliquerFallbackMassif(int $tailleLot = 1000): int
    {
        $traites = 0;
        $coordonneesMemo = [];

        $traiter = function (int $villeId, ?int $entrepriseId = null, ?int $etablissementId = null) use (&$traites, &$coordonneesMemo): void {
            if (! array_key_exists($villeId, $coordonneesMemo)) {
                $coordonneesMemo[$villeId] = $this->coordonneesCommune($villeId);
            }

            if ($coordonneesMemo[$villeId] === null) {
                return;
            }

            $this->appliquerCoordonnees($coordonneesMemo[$villeId], $entrepriseId, $etablissementId);
            $traites++;
        };

        Entreprise::query()
            ->whereNotNull('ville_id')
            ->whereNull('latlng')
            ->orderBy('id')
            ->chunkById($tailleLot, function ($entreprises) use ($traiter) {
                foreach ($entreprises as $entreprise) {
                    $traiter($entreprise->ville_id, entrepriseId: $entreprise->id);
                }
            });

        Etablissement::query()
            ->whereNotNull('ville_id')
            ->whereNull('latlng')
            ->orderBy('id')
            ->chunkById($tailleLot, function ($etablissements) use ($traiter) {
                foreach ($etablissements as $etablissement) {
                    $traiter($etablissement->ville_id, etablissementId: $etablissement->id);
                }
            });

        return $traites;
    }
}
