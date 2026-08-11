<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\EntrepriseResource\Pages\EditEntreprise;
use App\Filament\Resources\EntrepriseResource\Pages\ListEntreprises;
use App\Filament\Resources\EntrepriseResource\RelationManagers\SectionsRelationManager;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Section;
use App\Models\SectionReorder;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class EntrepriseResourceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::Admin);
    }

    private function entreprise(): Entreprise
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $specialite = Specialite::create(['libelle' => 'Cuisine lyonnaise', 'slug' => 'cuisine-lyonnaise', 'description' => null]);

        $entreprise = Entreprise::create([
            'siren' => '356000002',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
        $entreprise->specialites()->attach($specialite);

        return $entreprise;
    }

    public function test_admin_can_list_entreprises(): void
    {
        $entreprise = $this->entreprise();

        Livewire::actingAs($this->admin, 'web')
            ->test(ListEntreprises::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$entreprise]);
    }

    public function test_admin_can_open_edit_page(): void
    {
        $entreprise = $this->entreprise();

        Livewire::actingAs($this->admin, 'web')
            ->test(EditEntreprise::class, ['record' => $entreprise->getRouteKey()])
            ->assertSuccessful()
            ->assertFormFieldExists('denomination')
            ->assertFormFieldExists('visible');
    }

    public function test_siren_siret_cannot_be_edited(): void
    {
        $entreprise = $this->entreprise();

        Livewire::actingAs($this->admin, 'web')
            ->test(EditEntreprise::class, ['record' => $entreprise->getRouteKey()])
            ->assertFormFieldIsDisabled('siren');
    }

    public function test_admin_can_toggle_a_section_override_for_a_fiche(): void
    {
        $entreprise = $this->entreprise();
        $section = Section::factory()->create(['code' => 'fiche_test', 'libelle' => 'Test', 'ordre' => 10]);
        $reorder = SectionReorder::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'section_id' => $section->id,
            'position' => 5,
            'visible' => false,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(SectionsRelationManager::class, [
                'ownerRecord' => $entreprise,
                'pageClass' => EditEntreprise::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$reorder]);

        $this->assertFalse($entreprise->sections()->first(
            fn (array $item): bool => $item['section']->is($section)
        )['visible']);
    }
}
