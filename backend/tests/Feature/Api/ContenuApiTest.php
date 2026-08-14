<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\ActiviteNaf;
use App\Models\ContenuIa;
use App\Models\Entreprise;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ContenuApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private static int $nafCounter = 0;

    private function entreprise(): Entreprise
    {
        self::$nafCounter++;
        $naf = ActiviteNaf::create(['code' => str_pad((string) self::$nafCounter, 6, '0', STR_PAD_LEFT), 'section' => 'I', 'libelle' => 'Restauration traditionnelle']);

        return Entreprise::create([
            'siren' => '356000012',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => true,
        ]);
    }

    public function test_public_can_list_published_contenus_ia_for_an_entreprise(): void
    {
        $entreprise = $this->entreprise();
        ContenuIa::create(['entity_type' => $entreprise::class, 'entity_id' => $entreprise->id, 'type_contenu' => 'presentation', 'contenu' => 'Text', 'statut' => 'published']);
        ContenuIa::create(['entity_type' => $entreprise::class, 'entity_id' => $entreprise->id, 'type_contenu' => 'histoire', 'contenu' => 'Draft', 'statut' => 'draft']);

        $this->getJson("/api/v1/contenus-ia?entity_type=entreprise&entity_id={$entreprise->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type_contenu', 'presentation');
    }

    public function test_public_can_list_faq_for_an_entreprise(): void
    {
        $entreprise = $this->entreprise();
        Faq::create(['entity_type' => $entreprise::class, 'entity_id' => $entreprise->id, 'question' => 'Horaires ?', 'reponse' => '9h-19h', 'visible' => true]);

        $this->getJson("/api/v1/faq?entity_type=entreprise&entity_id={$entreprise->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.question', 'Horaires ?');
    }

    public function test_editor_can_create_but_not_publish_content(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);
        $entreprise = $this->entreprise();

        $this->actingAs($editor, 'sanctum')
            ->postJson('/api/v1/contenus-ia', [
                'entity_type' => 'entreprise',
                'entity_id' => $entreprise->id,
                'type_contenu' => 'presentation',
                'contenu' => 'Text',
                'statut' => 'draft',
            ])
            ->assertStatus(201);
    }

    public function test_polymorphic_entity_type_is_whitelisted(): void
    {
        $this->getJson('/api/v1/faq?entity_type=user&entity_id=1')
            ->assertStatus(422);
    }
}
