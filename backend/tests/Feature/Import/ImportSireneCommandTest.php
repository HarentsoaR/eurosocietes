<?php

namespace Tests\Feature\Import;

use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Import;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportSireneCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);
        $lyon = Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
        $lyon->codePostaux()->create(['code_postal' => '69001']);
        ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration']);
    }

    public function test_commande_importe_un_fichier_et_met_a_jour_les_compteurs(): void
    {
        $chemin = dirname(__DIR__, 2).'/fixtures/unites_test.csv';

        $this->artisan('import:sirene', ['--type' => 'unites', '--file' => $chemin, '--taille-lot' => 2])
            ->assertSuccessful();

        $this->assertSame(2, Entreprise::count());

        $import = Import::latest('id')->first();
        $this->assertSame('completed', $import->statut);
        $this->assertSame(2, $import->lignes_inserees);
        $this->assertSame(1, $import->lignes_radiees);
        $this->assertSame(3, $import->lignes_traitees);
    }

    public function test_reprise_apres_interruption_depuis_resume_state(): void
    {
        $chemin = dirname(__DIR__, 2).'/fixtures/unites_test.csv';

        // Simule un import interrompu après 1 ligne (la première, déjà insérée)
        $interrompu = Import::create([
            'type' => 'sirene_unites',
            'statut' => 'partial',
            'lignes_traitees' => 1,
            'lignes_inserees' => 1,
            'resume_state' => ['dernier_offset' => 1],
        ]);
        Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);

        $this->artisan('import:sirene', ['--type' => 'unites', '--file' => $chemin, '--resume' => true, '--taille-lot' => 2])
            ->assertSuccessful();

        // Les 3 lignes du fichier sont traitées au total (1 avant + 2 après), pas de doublon
        $this->assertSame(2, Entreprise::count());
        $this->assertSame(3, $interrompu->fresh()->lignes_traitees);
        $this->assertSame(2, $interrompu->fresh()->lignes_inserees);
        $this->assertSame('completed', $interrompu->fresh()->statut);
    }

    public function test_reprise_deja_complete_bascule_immediatement_en_terminé(): void
    {
        $chemin = dirname(__DIR__, 2).'/fixtures/unites_test.csv';

        $complet = Import::create([
            'type' => 'sirene_unites',
            'statut' => 'partial',
            'lignes_total' => 3,
            'lignes_traitees' => 3,
            'lignes_inserees' => 2,
            'lignes_radiees' => 1,
            'resume_state' => ['dernier_offset' => 3],
        ]);
        Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul-356000000', 'etat_administratif' => 'A', 'visible' => true]);
        Entreprise::create(['siren' => '356000018', 'denomination' => 'Boulangerie Pierre', 'slug' => 'boulangerie-pierre-356000018', 'etat_administratif' => 'A', 'visible' => true]);

        $this->artisan('import:sirene', ['--type' => 'unites', '--file' => $chemin, '--resume' => true, '--taille-lot' => 2])
            ->assertSuccessful();

        $this->assertSame(2, Entreprise::count());
        $this->assertSame('completed', $complet->fresh()->statut);
        $this->assertNotNull($complet->fresh()->completed_at);
    }
}
