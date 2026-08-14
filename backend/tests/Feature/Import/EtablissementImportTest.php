<?php

namespace Tests\Feature\Import;

use App\Import\ImportService;
use App\Models\ActiviteNaf;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Etablissement;
use App\Models\Import;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtablissementImportTest extends TestCase
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
        $lyon->codePostaux()->create(['code_postal' => '69002']);
        ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration']);

        Entreprise::create(['siren' => '356000000', 'denomination' => 'Boulangerie Paul', 'slug' => 'boulangerie-paul', 'etat_administratif' => 'A', 'visible' => true]);
        Entreprise::create(['siren' => '356000018', 'denomination' => 'Boulangerie Pierre', 'slug' => 'boulangerie-pierre', 'etat_administratif' => 'A', 'visible' => true]);
    }

    public function test_importe_les_etablissements(): void
    {
        $import = Import::create(['type' => 'sirene_etablissements', 'statut' => 'processing']);
        $service = new ImportService;

        $lignes = [
            ['siren' => '356000000', 'siret' => '35600000000006', 'etablissementSiege' => 'true', 'etatAdministratifEtablissement' => 'A', 'activitePrincipaleEtablissement' => '56.10A', 'enseigne1Etablissement' => 'Boulangerie Paul', 'codePostalEtablissement' => '69001', 'libelleCommuneEtablissement' => 'Lyon', 'codeCommuneEtablissement' => '69123'],
            ['siren' => '356000018', 'siret' => '35600001800008', 'etablissementSiege' => 'true', 'etatAdministratifEtablissement' => 'A', 'activitePrincipaleEtablissement' => '56.10A', 'enseigne1Etablissement' => '', 'codePostalEtablissement' => '69002', 'libelleCommuneEtablissement' => 'Lyon', 'codeCommuneEtablissement' => '69123'],
        ];

        $stats = $service->importerEtablissements($lignes, $import);

        $this->assertSame(2, $stats['inserees']);
        $this->assertSame(2, Etablissement::count());

        $siege = Etablissement::where('siret', '35600000000006')->first();
        $this->assertTrue($siege->est_siege);
        $this->assertSame('356000000', $siege->entreprise->siren);
        $this->assertSame('Lyon', $siege->ville->libelle);
    }
}
