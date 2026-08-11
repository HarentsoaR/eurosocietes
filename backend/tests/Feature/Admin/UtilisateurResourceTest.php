<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\UtilisateurResource\Pages\CreateUtilisateur;
use App\Filament\Resources\UtilisateurResource\Pages\EditUtilisateur;
use App\Filament\Resources\UtilisateurResource\Pages\ListUtilisateurs;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class UtilisateurResourceTest extends TestCase
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

    private function roleId(Role $role): int
    {
        return RoleModel::findByName($role->value, 'api')->id;
    }

    public function test_admin_can_list_utilisateurs(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(ListUtilisateurs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->admin]);
    }

    public function test_admin_can_create_an_utilisateur_with_a_role(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(CreateUtilisateur::class)
            ->fillForm([
                'name' => 'Jane Do',
                'email' => 'jane@example.com',
                'password' => 's3cret-password',
                'password_confirmation' => 's3cret-password',
                'roles' => [$this->roleId(Role::Editor)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::Editor->value));
    }

    public function test_creation_requires_a_matching_password(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(CreateUtilisateur::class)
            ->fillForm([
                'name' => 'Mismatch',
                'email' => 'mismatch@example.com',
                'password' => 's3cret-password',
                'password_confirmation' => 'different',
            ])
            ->call('create')
            ->assertHasFormErrors(['password_confirmation']);
    }

    public function test_admin_can_edit_roles_of_an_utilisateur(): void
    {
        $target = User::factory()->create();
        $target->assignRole(Role::User);

        Livewire::actingAs($this->admin, 'web')
            ->test(EditUtilisateur::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'roles' => [$this->roleId(Role::Editor)],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($target->fresh()->hasRole(Role::Editor->value));
        $this->assertFalse($target->fresh()->hasRole(Role::User->value));
    }

    public function test_admin_cannot_delete_his_own_account(): void
    {
        Livewire::actingAs($this->admin, 'web')
            ->test(ListUtilisateurs::class)
            ->assertTableActionHidden('delete', $this->admin);
    }

    public function test_admin_can_delete_another_utilisateur(): void
    {
        $target = User::factory()->create();

        Livewire::actingAs($this->admin, 'web')
            ->test(ListUtilisateurs::class)
            ->callTableAction('delete', $target)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('utilisateurs', ['id' => $target->id]);
    }

    public function test_admin_can_force_a_password_reset_on_another_utilisateur(): void
    {
        Notification::fake();

        $target = User::factory()->create();

        Livewire::actingAs($this->admin, 'web')
            ->test(ListUtilisateurs::class)
            ->callTableAction('send_reset_link', $target)
            ->assertHasNoTableActionErrors();

        Notification::assertSentTo($target, ResetPassword::class);
    }
}
