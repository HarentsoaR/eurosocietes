<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\EspaceVert;
use App\Models\Monument;
use App\Models\Pays;
use App\Models\Quartier;
use App\Models\Region;
use App\Models\Ville;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoireTest extends TestCase
{
    use RefreshDatabase;

    private function ville(): Ville
    {
        $france = Pays::create(['code_iso2' => 'FR', 'code_iso3' => 'FRA', 'code_insee' => '99100', 'libelle' => 'France', 'slug' => 'france']);
        $region = Region::create(['code' => '84', 'libelle' => 'Auvergne-Rhône-Alpes', 'slug' => 'auvergne-rhone-alpes', 'pays_id' => $france->id]);
        $departement = Departement::create(['code' => '69', 'libelle' => 'Rhône', 'slug' => 'rhone', 'region_id' => $region->id]);

        return Ville::create(['code_insee' => '69123', 'libelle' => 'Lyon', 'slug' => 'lyon', 'departement_id' => $departement->id]);
    }

    public function test_quartiers_monuments_espaces_verts(): void
    {
        $ville = $this->ville();

        $quartier = Quartier::create(['ville_id' => $ville->id, 'libelle' => 'Presqu\'île', 'slug' => 'presqu-ile', 'description' => null]);
        $monument = Monument::create(['ville_id' => $ville->id, 'libelle' => 'Basilique Notre-Dame', 'type' => 'église', 'ref_merimee' => 'PA00117960']);
        $espaceVert = EspaceVert::create(['ville_id' => $ville->id, 'quartier_id' => $quartier->id, 'libelle' => 'Parc de la Tête d\'Or', 'type' => 'parc']);

        $this->assertTrue($ville->quartiers->contains($quartier));
        $this->assertTrue($ville->monuments->contains($monument));
        $this->assertTrue($ville->espacesVerts->contains($espaceVert));
        $this->assertTrue($espaceVert->quartier->is($quartier));
    }

    public function test_slug_quartier_unique(): void
    {
        $ville = $this->ville();
        Quartier::create(['ville_id' => $ville->id, 'libelle' => 'Presqu\'île', 'slug' => 'presqu-ile', 'description' => null]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Quartier::create(['ville_id' => $ville->id, 'libelle' => 'Autre', 'slug' => 'presqu-ile', 'description' => null]);
    }
}
