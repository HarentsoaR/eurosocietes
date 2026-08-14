<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use App\Models\VilleCodePostal;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferentielGeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_hierarchie_pays_region_departement_ville(): void
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $auvergne = $france->regions()->create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes']);
        $rhone = $auvergne->departements()->create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone']);
        $lyon = $rhone->villes()->create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon']);

        $this->assertTrue($lyon->departement->is($rhone));
        $this->assertTrue($rhone->region->is($auvergne));
        $this->assertTrue($auvergne->pays->is($france));
        $this->assertCount(1, $france->regions);
        $this->assertCount(1, $auvergne->departements);
        $this->assertCount(1, $rhone->villes);
    }

    public function test_une_ville_peut_avoir_plusieurs_codes_postaux(): void
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '11', 'libelle' => 'Île-de-France', 'slug' => 'ile-de-france', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '75', 'libelle' => 'Paris', 'slug' => 'paris', 'region_id' => $region->id]);
        $ville = Ville::create(['code_insee' => '75056', 'libelle' => 'Paris', 'slug' => 'paris', 'departement_id' => $departement->id]);

        $ville->codePostaux()->create(['code_postal' => '75001']);
        $ville->codePostaux()->create(['code_postal' => '75002']);

        $this->assertCount(2, $ville->codePostaux);
        $this->assertInstanceOf(VilleCodePostal::class, $ville->codePostaux->first());
    }

    public function test_slug_ville_unique(): void
    {
        $this->expectException(QueryException::class);
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);

        Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
        Ville::create(['code_insee' => '69124', 'libelle' => 'Lyon 2', 'slug' => 'lyon', 'departement_id' => $departement->id]);
    }
}
