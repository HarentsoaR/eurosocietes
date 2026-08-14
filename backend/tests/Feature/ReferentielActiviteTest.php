<?php

namespace Tests\Feature;

use App\Models\ActiviteNaf;
use App\Models\Specialite;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferentielActiviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_creer_une_activite_naf_complete(): void
    {
        $naf = ActiviteNaf::create([
            'code' => '56.10A',
            'section' => 'I',
            'section_libelle' => 'Hébergement et restauration',
            'division' => '56',
            'division_libelle' => 'Restauration',
            'groupe' => '56.1',
            'groupe_libelle' => 'Restaurants et services de restauration mobile',
            'classe' => '56.10',
            'classe_libelle' => 'Restaurants et services de restauration mobile',
            'libelle' => 'Restauration traditionnelle',
        ]);

        $this->assertDatabaseHas('activites_naf', ['code' => '56.10A']);
        $this->assertSame('Restauration traditionnelle', $naf->libelle);
    }

    public function test_specialite_unique(): void
    {
        $this->expectException(QueryException::class);

        Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise', 'description' => null]);
        Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise-2', 'description' => null]);
    }
}
