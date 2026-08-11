<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_non_admin_cannot_access_the_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::User);

        $this->actingAs($user, 'web')
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_editor_cannot_access_the_panel(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor, 'web')
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_admin_can_access_the_panel_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);

        $response = $this->actingAs($admin, 'web')
            ->get('/admin');

        $response->assertOk()
            ->assertSee('EuroSocietes');
    }

    public function test_admin_panel_login_route_exists(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
