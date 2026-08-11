<?php

namespace Tests\Feature\Import;

use App\Import\CogImporter;
use App\Models\Departement;
use App\Models\Pays;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CogImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);
        Departement::create(['code' => '75', 'libelle' => 'Paris', 'slug' => 'paris', 'region_id' => $region->id]);
    }

    public function test_importe_les_communes_et_codes_postaux(): void
    {
        $importer = new CogImporter();

        $stats = $importer->importer(
            dirname(__DIR__, 2).'/fixtures/cog_communes.csv',
            dirname(__DIR__, 2).'/fixtures/cog_geofla.csv'
        );

        $this->assertSame(2, $stats['villes_inserees']);

        $lyon = Ville::where('code_insee', '69123')->first();
        $this->assertNotNull($lyon);
        $this->assertSame('Lyon', $lyon->libelle);
        $this->assertCount(2, $lyon->codePostaux);
        $this->assertSame('69001', $lyon->codePostaux->sortBy('code_postal')->first()->code_postal);
        $this->assertSame('69', $lyon->departement->code);
    }

    public function test_reimport_est_idempotent(): void
    {
        $importer = new CogImporter();
        $chemin = dirname(__DIR__, 2).'/fixtures/cog_communes.csv';

        $importer->importer($chemin, dirname(__DIR__, 2).'/fixtures/cog_geofla.csv');
        $stats = $importer->importer($chemin, dirname(__DIR__, 2).'/fixtures/cog_geofla.csv');

        $this->assertSame(0, $stats['villes_inserees']);
        $this->assertSame(3, $stats['villes_maj']);
        $this->assertSame(2, Ville::count());
    }
}
