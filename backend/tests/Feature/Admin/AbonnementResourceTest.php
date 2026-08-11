<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\AbonnementResource\Pages\EditAbonnement;
use App\Filament\Resources\AbonnementResource\Pages\ListAbonnements;
use App\Models\Abonnement;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class AbonnementResourceTest extends TestCase
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

    private function abonnement(): Abonnement
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $entreprise = Entreprise::create([
            'siren' => '356000010',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);

        return Abonnement::create([
            'entreprise_id' => $entreprise->id,
            'plan' => 'premium',
            'statut' => 'actif',
            'stripe_id' => 'sub_123456',
            'date_debut' => now()->subMonth(),
            'date_fin' => now()->addMonth(),
            'renouvellement_auto' => true,
        ]);
    }

    public function test_admin_can_list_abonnements(): void
    {
        $abonnement = $this->abonnement();

        Livewire::actingAs($this->admin, 'web')
            ->test(ListAbonnements::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$abonnement]);
    }

    public function test_admin_can_toggle_auto_renewal(): void
    {
        $abonnement = $this->abonnement();

        Livewire::actingAs($this->admin, 'web')
            ->test(EditAbonnement::class, ['record' => $abonnement->getRouteKey()])
            ->fillForm(['renouvellement_auto' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('abonnements', ['id' => $abonnement->id, 'renouvellement_auto' => false]);
    }

    public function test_stripe_id_is_read_only(): void
    {
        $abonnement = $this->abonnement();

        Livewire::actingAs($this->admin, 'web')
            ->test(EditAbonnement::class, ['record' => $abonnement->getRouteKey()])
            ->assertFormFieldIsDisabled('stripe_id');
    }
}
