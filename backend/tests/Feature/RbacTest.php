<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class RbacTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::Admin);

        $this->user = User::factory()->create();
        $this->user->assignRole(Role::User);
    }

    public function test_admin_can_access_admin_route(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/ping')
            ->assertStatus(200)
            ->assertJsonPath('message', 'ok');
    }

    public function test_user_cannot_access_admin_route(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/ping')
            ->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_route(): void
    {
        $this->getJson('/api/v1/admin/ping')->assertStatus(401);
    }

    public function test_admin_has_admin_permissions(): void
    {
        $this->assertTrue($this->admin->hasPermissionTo(Permission::CompanyDelete->value));
        $this->assertTrue($this->admin->hasPermissionTo(Permission::ContentPublish->value));
    }

    public function test_user_has_limited_permissions(): void
    {
        $this->assertTrue($this->user->hasPermissionTo(Permission::CompanyView->value));
        $this->assertFalse($this->user->hasPermissionTo(Permission::CompanyDelete->value));
    }
}
