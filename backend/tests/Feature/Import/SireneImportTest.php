<?php

namespace Tests\Feature\Import;

use App\Import\ImportService;
use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Import;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SireneImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement69 = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);
        $departement75 = Departement::create(['code' => '75', 'libelle' => 'Paris', 'slug' => 'paris', 'region_id' => $region->id]);

        $lyon = Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement69->id]);
        $lyon->codePostaux()->create(['code_postal' => '69001']);
        $lyon->codePostaux()->create(['code_postal' => '69002']);
        Ville::create(['code_insee' => '75056', 'libelle' => 'Paris', 'slug' => 'paris', 'departement_id' => $departement75->id]);
        ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration']);
    }

    private function importeLignes(array $lignes): array
    {
        $import = Import::create(['type' => 'sirene_unites', 'statut' => 'processing']);
        $service = new ImportService();

        return $service->importerUnites($lignes, $import);
    }

    public function test_importe_les_unites_et_cree_les_entreprises(): void
    {
        $lignes = [
            ['siren' => '356000000', 'denominationUniteLegale' => 'Boulangerie Paul', 'nomUniteLegale' => '', 'prenom1UniteLegale' => '', 'etatAdministratifUniteLegale' => 'A', 'activitePrincipaleUniteLegale' => '56.10A', 'codePostalUniteLegale' => '69001', 'libelleCommuneUniteLegale' => 'Lyon', 'codeCommuneUniteLegale' => '69123'],
            ['siren' => '356000018', 'denominationUniteLegale' => 'Boulangerie Pierre', 'nomUniteLegale' => '', 'prenom1UniteLegale' => '', 'etatAdministratifUniteLegale' => 'A', 'activitePrincipaleUniteLegale' => '56.10A', 'codePostalUniteLegale' => '69002', 'libelleCommuneUniteLegale' => 'Lyon', 'codeCommuneUniteLegale' => '69123'],
        ];

        $stats = $this->importeLignes($lignes);

        $this->assertSame(2, $stats['inserees']);
        $this->assertSame(2, Entreprise::count());

        $paul = Entreprise::where('siren', '356000000')->first();
        $this->assertSame('Boulangerie Paul', $paul->denomination);
        $this->assertSame('A', $paul->etat_administratif);
        $this->assertSame('56.10A', $paul->activiteNaf->code);
        $this->assertSame('Lyon', $paul->ville->libelle);
    }

    public function test_reimport_met_a_jour_sans_doublon(): void
    {
        $ligne = ['siren' => '356000000', 'denominationUniteLegale' => 'Boulangerie Paul', 'nomUniteLegale' => '', 'prenom1UniteLegale' => '', 'etatAdministratifUniteLegale' => 'A', 'activitePrincipaleUniteLegale' => '56.10A', 'codePostalUniteLegale' => '69001', 'libelleCommuneUniteLegale' => 'Lyon', 'codeCommuneUniteLegale' => '69123'];

        $this->importeLignes([$ligne]);
        $stats = $this->importeLignes([$ligne]);

        $this->assertSame(0, $stats['inserees']);
        $this->assertSame(1, $stats['maj']);
        $this->assertSame(1, Entreprise::count());
    }

    public function test_radiee_passe_en_etat_C_et_soft_delete(): void
    {
        $lignes = [
            ['siren' => '356000026', 'denominationUniteLegale' => 'Societe Radiee', 'nomUniteLegale' => '', 'prenom1UniteLegale' => '', 'etatAdministratifUniteLegale' => 'C', 'activitePrincipaleUniteLegale' => '56.10A', 'codePostalUniteLegale' => '75001', 'libelleCommuneUniteLegale' => 'Paris', 'codeCommuneUniteLegale' => '75056'],
        ];

        $stats = $this->importeLignes($lignes);

        $this->assertSame(1, $stats['radiees']);
        $this->assertSame(0, Entreprise::count());
        $this->assertSame(1, Entreprise::withTrashed()->count());

        $radiee = Entreprise::withTrashed()->where('siren', '356000026')->first();
        $this->assertSame('C', $radiee->etat_administratif);
        $this->assertNotNull($radiee->deleted_at);
    }
}
