<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\ImportResource;
use App\Filament\Resources\ImportResource\Pages\ListImports;
use App\Filament\Resources\ImportResource\Pages\ViewImport;
use App\Filament\Resources\ImportResource\RelationManagers\LogsRelationManager;
use App\Models\Import;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ImportResourceTest extends TestCase
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

    public function test_admin_can_list_imports(): void
    {
        $import = Import::create([
            'type' => 'unites_legales',
            'source' => 'sirene',
            'statut' => 'success',
            'lignes_total' => 2,
            'lignes_inserees' => 2,
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(ListImports::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$import]);
    }

    public function test_admin_can_view_import_logs(): void
    {
        $import = Import::create([
            'type' => 'etablissements',
            'source' => 'sirene',
            'statut' => 'partial',
            'lignes_total' => 3,
            'lignes_erreur' => 1,
        ]);
        $log = ImportLog::create([
            'import_id' => $import->id,
            'niveau' => 'error',
            'message' => 'SIREN mal formé',
            'siren' => '999',
            'ligne' => 4,
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin, 'web')
            ->test(LogsRelationManager::class, [
                'ownerRecord' => $import,
                'pageClass' => ViewImport::class,
            ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_the_resource_is_read_only(): void
    {
        $this->assertFalse(ImportResource::canCreate());
        $this->assertFalse(ImportResource::canDeleteAny());
    }
}
