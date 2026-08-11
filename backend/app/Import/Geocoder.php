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

    /**
     * Copie les coordonnées de la commune vers l'entreprise et/ou l'établissement (fallback).
     */
    public function appliquerFallback(int $villeId, ?int $entrepriseId = null, ?int $etablissementId = null): void
    {
        $coordonnees = $this->coordonneesCommune($villeId);
        if ($coordonnees === null) {
            return;
        }

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

    /**
     * Applique le fallback commune sur tous les sièges d'entreprise non géolocalisés.
     */
    public function appliquerFallbackMassif(int $tailleLot = 1000): int
    {
        $traites = 0;

        Entreprise::query()
            ->whereNotNull('ville_id')
            ->whereNull('latlng')
            ->orderBy('id')
            ->chunkById($tailleLot, function ($entreprises) use (&$traites) {
                foreach ($entreprises as $entreprise) {
                    $this->appliquerFallback($entreprise->ville_id, entrepriseId: $entreprise->id);
                    $traites++;
                }
            });

        Etablissement::query()
            ->whereNotNull('ville_id')
            ->whereNull('latlng')
            ->orderBy('id')
            ->chunkById($tailleLot, function ($etablissements) use (&$traites) {
                foreach ($etablissements as $etablissement) {
                    $this->appliquerFallback($etablissement->ville_id, etablissementId: $etablissement->id);
                    $traites++;
                }
            });

        return $traites;
    }
}
