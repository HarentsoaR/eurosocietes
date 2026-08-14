<?php

namespace Tests\Feature\Api;

use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Specialite;
use App\Models\Ville;
use App\Models\VilleCodePostal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ReferentielApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_public_can_list_naf_activites(): void
    {
        ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'section_libelle' => 'Hébergement et restauration', 'libelle' => 'Restauration traditionnelle']);

        $this->getJson('/api/v1/naf-activites')
            ->assertOk()
            ->assertJsonPath('data.0.code', '56.10A');
    }

    public function test_public_can_list_specialites(): void
    {
        Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise']);

        $this->getJson('/api/v1/specialites')
            ->assertOk()
            ->assertJsonPath('data.0.libelle', 'Cuisine lyonnaise');
    }

    public function test_pays_regions_departements_villes_hierarchy(): void
    {
        $pays = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $pays->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);
        $ville = Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id, 'population' => 520000]);
        VilleCodePostal::create(['ville_id' => $ville->id, 'code_postal' => '69003']);

        $this->getJson('/api/v1/pays')->assertOk()->assertJsonPath('data.0.libelle', 'France');
        $this->getJson("/api/v1/pays/{$pays->id}/regions")->assertOk()->assertJsonPath('data.0.libelle', 'Auvergne-Rhône-Alpes');
        $this->getJson("/api/v1/regions/{$region->id}/departements")->assertOk()->assertJsonPath('data.0.libelle', 'Rhône');
        $this->getJson("/api/v1/departements/{$departement->id}/villes")->assertOk()->assertJsonPath('data.0.libelle', 'Lyon');
        $this->getJson("/api/v1/villes/{$ville->id}")->assertOk()->assertJsonPath('data.code_insee', '69123');
        $this->getJson("/api/v1/villes/{$ville->id}/code-postaux")->assertOk()->assertJsonPath('data.0.code_postal', '69003');
    }

    public function test_villes_search_by_q(): void
    {
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france'])->id])->id]);
        Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
        Ville::create(['code_insee' => '69124', 'libelle' => 'Lyon 2e', 'slug' => 'lyon-2e', 'departement_id' => $departement->id]);

        $this->getJson('/api/v1/villes?q=lyon')->assertOk()->assertJsonCount(2, 'data');
    }
}
