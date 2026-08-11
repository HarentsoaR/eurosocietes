<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Filament\Resources\FaqResource\Pages\EditFaq;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class FaqResourceTest extends TestCase
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

    public function test_admin_can_list_faqs(): void
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $entreprise = Entreprise::create([
            'siren' => '356000003',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
        $faq = Faq::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'question' => 'Quels sont les horaires ?',
            'reponse' => 'Du lundi au vendredi.',
            'ordre' => 1,
            'visible' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListFaqs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$faq]);
    }

    public function test_admin_can_create_a_faq_for_an_entreprise(): void
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $entreprise = Entreprise::create([
            'siren' => '356000004',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(CreateFaq::class)
            ->fillForm([
                'entity_type' => $entreprise::class,
                'entity_id' => $entreprise->id,
                'question' => 'Quels sont les horaires ?',
                'reponse' => 'Du lundi au vendredi.',
                'ordre' => 1,
                'visible' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('faq', [
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'question' => 'Quels sont les horaires ?',
            'visible' => true,
        ]);
    }

    public function test_admin_can_edit_a_faq(): void
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $entreprise = Entreprise::create([
            'siren' => '356000005',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
        $faq = Faq::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'question' => 'Ancienne question',
            'reponse' => 'Ancienne réponse',
            'ordre' => 1,
            'visible' => true,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['visible' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('faq', ['id' => $faq->id, 'visible' => false]);
    }
}
