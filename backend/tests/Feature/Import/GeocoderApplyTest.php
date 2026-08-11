<?php

namespace Tests\Feature\Import;

use App\Import\Geocoder;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeocoderApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_appliquer_fallback_commune_sur_entreprise(): void
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);
        $ville = Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
        DB::statement('UPDATE villes SET latlng = ST_SetSRID(ST_MakePoint(4.8320, 45.7578), 4326)::geography WHERE id = ?', [$ville->id]);

        $entreprise = Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'ville_id' => $ville->id, 'visible' => true]);

        $geocoder = new Geocoder();
        $geocoder->appliquerFallback($ville->id, entrepriseId: $entreprise->id);

        $coordonnees = DB::table('entreprises')
            ->selectRaw('ST_Y(latlng::geometry) AS lat, ST_X(latlng::geometry) AS lng')
            ->where('id', $entreprise->id)
            ->first();

        $this->assertNotNull($coordonnees);
        $this->assertEqualsWithDelta(45.7578, (float) $coordonnees->lat, 0.0001);
        $this->assertEqualsWithDelta(4.8320, (float) $coordonnees->lng, 0.0001);
    }
}
