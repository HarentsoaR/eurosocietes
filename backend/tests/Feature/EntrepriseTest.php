<?php

namespace Tests\Feature;

use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Specialite;
use App\Models\Ville;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntrepriseTest extends TestCase
{
    use RefreshDatabase;

    private function ville(): Ville
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);

        return Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
    }

    public function test_creer_une_entreprise_avec_relations(): void
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $specialite = Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise', 'description' => null]);
        $ville = $this->ville();

        $entreprise = Entreprise::create([
            'siren' => '356000000',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'ville_id' => $ville->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);

        $entreprise->specialites()->attach($specialite);

        $this->assertTrue($entreprise->activiteNaf->is($naf));
        $this->assertTrue($entreprise->ville->is($ville));
        $this->assertCount(1, $entreprise->specialites);
        $this->assertDatabaseHas('entreprises', ['siren' => '356000000']);
    }

    public function test_scope_recherche_fulltext(): void
    {
        Entreprise::create([
            'siren' => '356000000',
            'denomination' => 'Boulangerie de Lyon',
            'slug' => 'boulangerie-de-lyon',
            'etat_administratif' => 'A',
            'visible' => true,
        ]);

        $resultats = Entreprise::recherche('boulangerie')->get();

        $this->assertCount(1, $resultats);
        $this->assertSame('Boulangerie de Lyon', $resultats->first()->denomination);
    }

    public function test_scope_actives(): void
    {
        Entreprise::create(['siren' => '356000000', 'denomination' => 'Active', 'slug' => 'active', 'etat_administratif' => 'A', 'visible' => true]);
        Entreprise::create(['siren' => '356000001', 'denomination' => 'Radiee', 'slug' => 'radiee', 'etat_administratif' => 'C', 'visible' => true]);

        $this->assertCount(1, Entreprise::actives()->get());
    }

    public function test_siren_mal_forme_rejete(): void
    {
        $this->expectException(QueryException::class);

        Entreprise::create(['siren' => 'ABC', 'denomination' => 'Test', 'slug' => 'test', 'etat_administratif' => 'A']);
    }

    public function test_soft_delete(): void
    {
        $entreprise = Entreprise::create(['siren' => '356000000', 'denomination' => 'Test', 'slug' => 'test', 'etat_administratif' => 'A', 'visible' => true]);
        $entreprise->delete();

        $this->assertCount(0, Entreprise::all());
        $this->assertCount(1, Entreprise::withTrashed()->get());
    }
}
