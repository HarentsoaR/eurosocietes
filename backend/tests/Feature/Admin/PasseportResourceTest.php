<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\PasseportResource\Pages\ListPasseports;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Passeport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class PasseportResourceTest extends TestCase
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

    private function passeport(bool $validated = false): array
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
        $entreprise = Entreprise::create([
            'siren' => '356000009',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
        $passeport = Passeport::create([
            'entreprise_id' => $entreprise->id,
            'statut' => $validated ? 'valide' : 'soumis',
            'score_confidence' => 75,
            'badges' => ['complet'],
            'is_validated' => $validated,
        ]);

        return [$passeport, $entreprise];
    }

    public function test_admin_can_validate_a_passeport(): void
    {
        [$passeport] = $this->passeport();

        Livewire::actingAs($this->admin, 'web')
            ->test(ListPasseports::class)
            ->callTableAction('valider', $passeport)
            ->assertSuccessful();

        $this->assertDatabaseHas('passeports', [
            'id' => $passeport->id,
            'statut' => 'valide',
            'is_validated' => true,
            'validateur_id' => $this->admin->id,
        ]);
        $this->assertNotNull($passeport->fresh()->validated_at);
    }

    public function test_validation_columns_are_populated_by_the_remote_admin(): void
    {
        $remote = User::factory()->create();
        $remote->assignRole(Role::Admin);

        [$passeport] = $this->passeport();

        Livewire::actingAs($remote, 'web')
            ->test(ListPasseports::class)
            ->callTableAction('valider', $passeport)
            ->assertSuccessful();

        $this->assertSame($remote->id, $passeport->fresh()->validateur_id);
    }
}