<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\ContenuIaResource\Pages\ListContenusIa;
use App\Jobs\RegenerateContenuIa;
use App\Models\ActiviteNaf;
use App\Models\ContenuIa;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ContenuIaResourceTest extends TestCase
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

        return Entreprise::create([
            'siren' => '356000006',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
    }

    public function test_admin_can_list_contenus_ia(): void
    {
        $entreprise = $this->entreprise();
        $contenu = ContenuIa::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'type_contenu' => 'description',
            'contenu' => 'Texte généré',
            'statut' => 'done',
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListContenusIa::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$contenu]);
    }

    public function test_regenerate_action_queues_the_job(): void
    {
        Queue::fake();

        $entreprise = $this->entreprise();
        $contenu = ContenuIa::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'type_contenu' => 'description',
            'contenu' => 'Texte généré',
            'statut' => 'done',
            'generated_at' => now(),
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListContenusIa::class)
            ->callTableAction('regenerer', $contenu)
            ->assertSuccessful();

        Queue::assertPushed(RegenerateContenuIa::class);
    }

    public function test_regenerate_job_resets_status_to_pending(): void
    {
        $entreprise = $this->entreprise();
        $contenu = ContenuIa::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'type_contenu' => 'description',
            'contenu' => 'Texte généré',
            'statut' => 'done',
            'generated_at' => now(),
        ]);

        (new RegenerateContenuIa($contenu))->handle();

        $this->assertDatabaseHas('contenus_ia', [
            'id' => $contenu->id,
            'statut' => 'pending',
            'generated_at' => null,
        ]);
    }
}
