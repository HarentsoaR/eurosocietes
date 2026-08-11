<?php

namespace Tests\Feature\Admin;

use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Section;
use App\Models\SectionReorder;
use App\Models\Specialite;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionTest extends TestCase
{
    use RefreshDatabase;

    private function entreprise(): Entreprise
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $specialite = Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise', 'description' => null]);

        $entreprise = Entreprise::create([
            'siren' => '356000001',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
        $entreprise->specialites()->attach($specialite);

        return $entreprise;
    }

    public function test_canonical_section_is_stored(): void
    {
        $section = Section::factory()->create(['code' => 'fiche_test', 'ordre' => 10, 'visible' => true]);

        $this->assertDatabaseHas('sections', ['code' => 'fiche_test', 'visible' => true]);
        $this->assertSame(10, $section->ordre);
    }

    public function test_section_code_is_unique(): void
    {
        Section::factory()->create(['code' => 'fiche_dupliquee']);
        $this->expectException(QueryException::class);

        Section::factory()->create(['code' => 'fiche_dupliquee']);
    }

    public function test_entity_can_hide_a_section(): void
    {
        $visible = Section::factory()->create(['code' => 'fiche_visible', 'visible' => true, 'ordre' => 10]);
        $hidden = Section::factory()->create(['code' => 'fiche_cachee', 'visible' => true, 'ordre' => 20]);
        $entreprise = $this->entreprise();

        SectionReorder::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'section_id' => $hidden->id,
            'position' => $hidden->ordre,
            'visible' => false,
        ]);

        $result = $entreprise->sections();

        $this->assertTrue($result->first(fn (array $item): bool => $item['section']->is($visible))['visible']);
        $this->assertFalse($result->first(fn (array $item): bool => $item['section']->is($hidden))['visible']);
    }

    public function test_entity_reordering_changes_section_order(): void
    {
        $first = Section::factory()->create(['code' => 'fiche_avant', 'ordre' => 10]);
        $second = Section::factory()->create(['code' => 'fiche_apres', 'ordre' => 20]);
        $entreprise = $this->entreprise();

        SectionReorder::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'section_id' => $second->id,
            'position' => 5,
            'visible' => true,
        ]);

        $codes = $entreprise->sections()
            ->map(fn (array $item): string => $item['section']->code)
            ->all();

        $this->assertLessThan(
            array_search($first->code, $codes, true),
            array_search($second->code, $codes, true),
        );
    }

    public function test_an_entity_cannot_override_a_section_twice(): void
    {
        $section = Section::factory()->create();
        $entreprise = $this->entreprise();

        SectionReorder::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'section_id' => $section->id,
            'position' => 1,
            'visible' => true,
        ]);

        $this->expectException(QueryException::class);

        SectionReorder::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'section_id' => $section->id,
            'position' => 2,
            'visible' => false,
        ]);
    }
}
