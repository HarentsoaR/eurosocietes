<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\ActiviteNaf;
use App\Models\Dirigeant;
use App\Models\Entreprise;
use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class EntrepriseApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private static int $counter = 0;

    private ?ActiviteNaf $nafCache = null;

    private function naf(): ActiviteNaf
    {
        return $this->nafCache ??= ActiviteNaf::create(['code' => '56.10A', 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);
    }

    private function entreprise(bool $visible = true): Entreprise
    {
        self::$counter++;

        return Entreprise::create([
            'siren' => (string) (356000000 + self::$counter),
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul-'.self::$counter,
            'activite_naf_id' => $this->naf()->id,
            'etat_administratif' => 'A',
            'visible' => $visible,
        ]);
    }

    public function test_public_can_list_visible_entreprises(): void
    {
        $this->entreprise();
        $this->entreprise(false);

        $this->getJson('/api/v1/entreprises')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.denomination', 'Boulangerie Paul');
    }

    public function test_public_can_view_a_visible_entreprise_and_its_relations(): void
    {
        $entreprise = $this->entreprise();
        Etablissement::create(['siret' => '35600000100011', 'nic' => '00011', 'entreprise_id' => $entreprise->id, 'est_siege' => true]);
        Dirigeant::create(['entreprise_id' => $entreprise->id, 'nom' => 'Paul', 'prenoms' => 'Martin', 'est_principal' => true]);

        $this->getJson("/api/v1/entreprises/{$entreprise->siren}")
            ->assertOk()
            ->assertJsonPath('data.denomination', 'Boulangerie Paul');
    }

    public function test_hidden_entreprise_is_404_for_public(): void
    {
        $hidden = $this->entreprise(false);

        $this->getJson("/api/v1/entreprises/{$hidden->siren}")->assertStatus(404);
    }

    public function test_admin_can_view_hidden_entreprise(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);
        $hidden = $this->entreprise(false);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/entreprises/{$hidden->siren}")
            ->assertOk();
    }

    public function test_guest_cannot_create_entreprise(): void
    {
        $this->postJson('/api/v1/entreprises', [])->assertStatus(401);
    }

    public function test_editor_cannot_create_entreprise(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor, 'sanctum')
            ->postJson('/api/v1/entreprises', ['siren' => '356000007'])
            ->assertStatus(403);
    }

    public function test_public_can_list_etablissements_and_dirigeants(): void
    {
        $entreprise = $this->entreprise();
        Etablissement::create(['siret' => '35600000100011', 'nic' => '00011', 'entreprise_id' => $entreprise->id, 'est_siege' => true]);
        Dirigeant::create(['entreprise_id' => $entreprise->id, 'nom' => 'Paul', 'est_principal' => true]);

        $this->getJson("/api/v1/entreprises/{$entreprise->siren}/etablissements")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/entreprises/{$entreprise->siren}/dirigeants")->assertOk()->assertJsonCount(1, 'data');
    }
}
