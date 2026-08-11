<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\HistoriqueResource;
use App\Filament\Resources\HistoriqueResource\Pages\ListHistoriques;
use App\Models\Historique;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class HistoriqueResourceTest extends TestCase
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

    public function test_admin_can_list_historique_entries(): void
    {
        $entry = Historique::create([
            'entity_type' => User::class,
            'entity_id' => $this->admin->id,
            'action' => 'update',
            'utilisateur_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListHistoriques::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$entry]);
    }

    public function test_the_resource_is_read_only(): void
    {
        $this->assertFalse(HistoriqueResource::canCreate());
        $this->assertFalse(HistoriqueResource::canEdit(new Historique));
        $this->assertFalse(HistoriqueResource::canDeleteAny());
    }

    public function test_no_create_or_edit_route_is_exposed(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get('/admin/historiques/create')
            ->assertNotFound();

        $this->actingAs($this->admin, 'web')
            ->get('/admin/historiques/1/edit')
            ->assertNotFound();
    }
}
