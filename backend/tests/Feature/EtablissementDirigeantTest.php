<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\Dirigeant;
use App\Models\Entreprise;
use App\Models\Etablissement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtablissementDirigeantTest extends TestCase
{
    use RefreshDatabase;

    private function entreprise(): Entreprise
    {
        return Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);
    }

    private function ville(): Ville
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);

        return Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
    }

    public function test_creer_etablissement_et_dirigeant(): void
    {
        $entreprise = $this->entreprise();

        $etablissement = Etablissement::create([
            'siret' => '35600000000015',
            'nic' => '00015',
            'entreprise_id' => $entreprise->id,
            'est_siege' => true,
            'ville_id' => $this->ville()->id,
            'code_postal' => '69001',
            'etat_administratif' => 'A',
        ]);

        $dirigeant = Dirigeant::create([
            'entreprise_id' => $entreprise->id,
            'qualite' => 'Gérant',
            'nom' => 'Dupont',
            'prenoms' => 'Jean',
            'est_principal' => true,
        ]);

        $this->assertTrue($entreprise->etablissements->contains($etablissement));
        $this->assertTrue($entreprise->dirigeants->contains($dirigeant));
        $this->assertTrue($etablissement->entreprise->is($entreprise));
    }

    public function test_siret_mal_forme_rejete(): void
    {
        $this->expectException(QueryException::class);

        Etablissement::create([
            'siret' => '123',
            'nic' => '00015',
            'entreprise_id' => $this->entreprise()->id,
            'est_siege' => false,
            'etat_administratif' => 'A',
        ]);
    }
}
