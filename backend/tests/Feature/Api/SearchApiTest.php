<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\ActiviteNaf;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function entreprise(string $siren, string $denomination, bool $visible = true): Entreprise
    {
        $naf = ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);

        return Entreprise::create([
            'siren' => $siren,
            'denomination' => $denomination,
            'slug' => str()->slug($denomination),
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => $visible,
        ]);
    }

    public function test_post_recherches_logs_a_search_event(): void
    {
        $this->postJson('/api/v1/recherches', ['terme' => 'plombier lyon', 'nb_resultats' => 3])
            ->assertStatus(201);

        $this->assertDatabaseHas('recherches', ['terme' => 'plombier lyon', 'nb_resultats' => 3]);
    }

    public function test_statistiques_requires_admin(): void
    {
        $this->getJson('/api/v1/statistiques')->assertStatus(401);

        $user = User::factory()->create();
        $user->assignRole(Role::User);
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/statistiques')->assertStatus(403);

        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);
        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/statistiques')->assertOk();
    }
}
