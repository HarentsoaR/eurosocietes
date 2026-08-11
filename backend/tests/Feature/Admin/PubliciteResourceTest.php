<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\PubliciteResource\Pages\ListPublicites;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\Publicite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class PubliciteResourceTest extends TestCase
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
            'siren' => '356000008',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
    }

    public function test_publicite_model_attaches_visuel_via_medialibrary(): void
    {
        $entreprise = $this->entreprise();
        $publicite = Publicite::create([
            'entreprise_id' => $entreprise->id,
            'titre' => 'Campagne été',
            'emplacement' => 'fiche_entreprise',
            'statut' => 'brouillon',
        ]);

        $publicite->addMediaFromString('proxyimage')
            ->usingFileName('banniere.jpg')
            ->toMediaCollection('visuels');

        $this->assertCount(1, $publicite->getMedia('visuels'));
        $this->assertSame('banniere.jpg', $publicite->getFirstMedia('visuels')->file_name);
    }

    public function test_admin_can_publish_a_publicite(): void
    {
        $publicite = Publicite::create([
            'entreprise_id' => $this->entreprise()->id,
            'titre' => 'Campagne été',
            'emplacement' => 'fiche_entreprise',
            'statut' => 'brouillon',
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListPublicites::class)
            ->callTableAction('publier', $publicite)
            ->assertSuccessful();

        $this->assertDatabaseHas('publicites', ['id' => $publicite->id, 'statut' => 'publie']);
    }
}