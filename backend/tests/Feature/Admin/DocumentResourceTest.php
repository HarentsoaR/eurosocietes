<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Models\ActiviteNaf;
use App\Models\Document;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class DocumentResourceTest extends TestCase
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
            'siren' => '356000007',
            'denomination' => 'Boulangerie Paul',
            'slug' => 'boulangerie-paul',
            'activite_naf_id' => $naf->id,
            'etat_administratif' => 'A',
            'visible' => false,
        ]);
    }

    public function test_document_model_attaches_media_via_medialibrary(): void
    {
        $entreprise = $this->entreprise();
        $document = Document::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'type' => 'statuts',
            'titre' => 'Statuts signés',
            'chemin' => '',
            'statut_validation' => 'en_attente',
        ]);

        $document->addMediaFromString('contenu du fichier PDF')
            ->usingFileName('statuts.pdf')
            ->toMediaCollection('fichiers');

        $this->assertCount(1, $document->getMedia('fichiers'));
        $this->assertSame('statuts.pdf', $document->getFirstMedia('fichiers')->file_name);
    }

    public function test_admin_can_list_documents(): void
    {
        $entreprise = $this->entreprise();
        $document = Document::create([
            'entity_type' => $entreprise::class,
            'entity_id' => $entreprise->id,
            'type' => 'kbis',
            'titre' => 'Kbis récent',
            'chemin' => '',
            'statut_validation' => 'en_attente',
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListDocuments::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$document]);
    }

    public function test_admin_can_open_document_create_page(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(CreateDocument::class)
            ->assertSuccessful()
            ->assertFormFieldExists('entity_type')
            ->assertFormFieldExists('fichiers');
    }
}
