<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    private array $credentials = [
        'name' => 'Alice Martin',
        'email' => 'alice@example.com',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    private function createUserWithRole(Role $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('endpoint-test')->plainTextToken;
    }

    /**
     * Sanctum's RequestGuard caches the resolved user in the shared app
     * container, so successive requests in one test must reset the guards or
     * the second request resolves the previous user. Production is unaffected
     * (fresh bootstrap per request); this is purely a test-harness concern.
     */
    private function withTokenRequest(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    /**
     * Endpoint: GET /api/v1/ping
     */
    public function test_ping_returns_pong_publicly(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertStatus(200)
            ->assertJson(['message' => 'pong']);
    }

    /**
     * Endpoint: POST /api/v1/register
     */
    public function test_register_creates_user_with_default_role_and_db_state(): void
    {
        $response = $this->postJson('/api/v1/register', $this->credentials);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alice Martin')
            ->assertJsonPath('data.email', 'alice@example.com')
            ->assertJsonPath('data.roles.0.key', Role::User->value);

        $this->assertDatabaseHas('utilisateurs', [
            'email' => 'alice@example.com',
        ]);

        $user = User::where('email', 'alice@example.com')->firstOrFail();
        $this->assertNotSame($this->credentials['password'], $user->password);
        $this->assertTrue(Hash::check($this->credentials['password'], $user->password));
        $this->assertTrue($user->hasRole(Role::User));
        $this->assertTrue($user->hasPermissionTo(Permission::CompanyView->value));
        $this->assertTrue($user->hasPermissionTo(Permission::ContentView->value));
        $this->assertFalse($user->hasPermissionTo(Permission::CompanyDelete->value));
    }

    public function test_register_never_creates_privileged_role(): void
    {
        $this->postJson('/api/v1/register', $this->credentials)->assertStatus(201);

        $user = User::where('email', 'alice@example.com')->firstOrFail();
        $this->assertCount(1, $user->getRoleNames());
        $this->assertSame([Role::User->value], $user->getRoleNames()->all());
        $this->assertFalse($user->hasRole(Role::Admin));
        $this->assertFalse($user->hasRole(Role::Editor));
        $this->assertFalse($user->hasRole(Role::Company));
    }

    public function test_register_does_not_expose_password_in_response(): void
    {
        $response = $this->postJson('/api/v1/register', $this->credentials);

        $response->assertStatus(201)
            ->assertJsonMissing(['password']);
    }

    /**
     * Endpoint: POST /api/v1/login
     */
    public function test_login_with_device_name_creates_token_with_name(): void
    {
        $this->postJson('/api/v1/register', $this->credentials);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'alice@example.com',
            'password' => 'Str0ng!Passw0rd',
            'device_name' => 'iPhone de Alice',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']])
            ->assertJsonPath('data.user.email', 'alice@example.com');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'name' => 'iPhone de Alice',
        ]);
    }

    public function test_login_works_for_admin_role(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Str0ng!Passw0rd',
        ]);
        $admin->assignRole(Role::Admin);

        $this->postJson('/api/v1/login', [
            'email' => 'admin@example.com',
            'password' => 'Str0ng!Passw0rd',
        ])->assertStatus(200)
            ->assertJsonPath('data.user.roles.0.key', Role::Admin->value);
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'inconnu@example.com',
            'password' => 'Str0ng!Passw0rd',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rate_limited_to_5_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'bruteforce@example.com',
                'password' => 'Wrong!Passw0rd',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/login', [
            'email' => 'bruteforce@example.com',
            'password' => 'Wrong!Passw0rd',
        ])->assertStatus(429);
    }

    public function test_register_rate_limited_to_5_per_minute(): void
    {
        // The limiter keys on email+IP and runs before validation, so the 1st
        // attempt succeeds (201) and attempts 2-5 fail unique validation (422)
        // but still consume the shared email+IP budget. The 6th is throttled.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/register', $this->credentials);
        }

        $this->postJson('/api/v1/register', $this->credentials)->assertStatus(429);
    }

    public function test_forgot_password_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/password/forgot', ['email' => 'alice@example.com'])
                ->assertStatus(200);
        }

        $this->postJson('/api/v1/password/forgot', ['email' => 'alice@example.com'])
            ->assertStatus(429);
    }

    public function test_reset_password_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/password/reset', [
                'token' => 'invalid',
                'email' => 'alice@example.com',
                'password' => 'N3w!Str0ngPassw0rd',
                'password_confirmation' => 'N3w!Str0ngPassw0rd',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/password/reset', [
            'token' => 'invalid',
            'email' => 'alice@example.com',
            'password' => 'N3w!Str0ngPassw0rd',
            'password_confirmation' => 'N3w!Str0ngPassw0rd',
        ])->assertStatus(429);
    }

    public function test_throttle_scoped_per_endpoint(): void
    {
        // Exhaust login budget for this email+IP, other endpoints stay reachable.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'scoped@example.com',
                'password' => 'Wrong!Passw0rd',
            ]);
        }

        $this->postJson('/api/v1/login', [
            'email' => 'scoped@example.com',
            'password' => 'Wrong!Passw0rd',
        ])->assertStatus(429);

        // A different email+IP budget is untouched.
        $this->postJson('/api/v1/login', [
            'email' => 'autre@example.com',
            'password' => 'Wrong!Passw0rd',
        ])->assertStatus(422);

        // The register budget for the same email is separate.
        $this->postJson('/api/v1/register', $this->credentials)->assertStatus(201);
    }

    /**
     * Endpoint: GET /api/v1/me
     */
    public function test_me_returns_roles_and_permissions_for_each_role(): void
    {
        foreach ([Role::Admin, Role::Editor, Role::Company, Role::User] as $role) {
            $user = $this->createUserWithRole($role);
            $permissions = Permission::forRole($role);

            $this->withTokenRequest($this->tokenFor($user))
                ->getJson('/api/v1/me')
                ->assertStatus(200)
                ->assertJsonPath('data.id', $user->id)
                ->assertJsonPath('data.email', $user->email)
                ->assertJsonPath('data.roles.0.key', $role->value)
                ->assertJsonPath('data.permissions', collect($permissions)->pluck('value')->all());
        }
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_unauthenticated_request_with_non_json_accept_returns_401(): void
    {
        // Clients sending Accept: */* (e.g. curl without headers) must still get
        // a JSON 401 on protected routes instead of a redirect-to-login 500.
        $this->get('/api/v1/me', ['Accept' => '*/*'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Non authentifié.']);
    }

    /**
     * Endpoint: POST /api/v1/logout
     */
    public function test_logout_revokes_token_in_db(): void
    {
        $user = $this->createUserWithRole(Role::User);
        $token = $this->tokenFor($user);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withTokenRequest($token)->postJson('/api/v1/logout')->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/logout')->assertStatus(401);
    }

    public function test_token_is_single_use_only(): void
    {
        $user = $this->createUserWithRole(Role::User);
        $token = $this->tokenFor($user);

        $this->withTokenRequest($token)->postJson('/api/v1/logout')->assertStatus(200);
        $this->withTokenRequest($token)->getJson('/api/v1/me')->assertStatus(401);
    }

    /**
     * Endpoint: GET /api/v1/admin/ping — role:admin
     */
    public function test_admin_ping_matrix_for_all_roles(): void
    {
        $cases = [
            [Role::Admin, 200],
            [Role::Editor, 403],
            [Role::Company, 403],
            [Role::User, 403],
        ];

        foreach ($cases as [$role, $expected]) {
            $user = $this->createUserWithRole($role);

            $response = $this->withTokenRequest($this->tokenFor($user))
                ->getJson('/api/v1/admin/ping');

            $response->assertStatus($expected);
            if ($expected === 200) {
                $response->assertJson(['message' => 'ok']);
            }
        }
    }

    public function test_admin_ping_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/ping')->assertStatus(401);
    }

    /**
     * Endpoint: POST /api/v1/password/forgot
     */
    public function test_forgot_stores_reset_token_and_sends_notification(): void
    {
        Notification::fake();
        $user = $this->createUserWithRole(Role::User);

        $this->postJson('/api/v1/password/forgot', ['email' => $user->email])
            ->assertStatus(200)
            ->assertJsonPath('message', __('passwords.sent'));

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        Notification::assertSentTo($user, \App\Notifications\ApiResetPasswordNotification::class);
    }

    public function test_forgot_returns_same_message_for_unknown_email(): void
    {
        $this->postJson('/api/v1/password/forgot', ['email' => 'inconnu@example.com'])
            ->assertStatus(200)
            ->assertJsonPath('message', __('passwords.sent'));
    }

    /**
     * Endpoint: POST /api/v1/password/reset
     */
    public function test_reset_changes_password_and_revokes_all_tokens(): void
    {
        $user = $this->createUserWithRole(Role::User);
        $token = $this->tokenFor($user);
        $resetToken = Password::createToken($user);

        $this->postJson('/api/v1/password/reset', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'N3w!Str0ngPassw0rd',
            'password_confirmation' => 'N3w!Str0ngPassw0rd',
        ])->assertStatus(200)
            ->assertJsonPath('message', __('passwords.reset'));

        $this->assertTrue(Hash::check('N3w!Str0ngPassw0rd', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_reset_rejects_invalid_token(): void
    {
        $user = $this->createUserWithRole(Role::User);

        $this->postJson('/api/v1/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'N3w!Str0ngPassw0rd',
            'password_confirmation' => 'N3w!Str0ngPassw0rd',
        ])->assertStatus(422)
            ->assertJsonPath('message', __('passwords.failed'));
    }

    /**
     * Full happy-path flow through the public surface.
     */
    public function test_complete_auth_flow_end_to_end(): void
    {
        // register
        $register = $this->postJson('/api/v1/register', $this->credentials);
        $register->assertStatus(201);

        // login
        $login = $this->postJson('/api/v1/login', [
            'email' => 'alice@example.com',
            'password' => 'Str0ng!Passw0rd',
        ]);
        $login->assertStatus(200);
        $token = $login->json('data.token');
        $this->assertIsString($token);

        // me with token
        $this->withTokenRequest($token)->getJson('/api/v1/me')
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'alice@example.com');

        // non-admin blocked from admin route
        $this->withTokenRequest($token)->getJson('/api/v1/admin/ping')->assertStatus(403);

        // logout revokes token
        $this->withTokenRequest($token)->postJson('/api/v1/logout')->assertStatus(200);
        $this->withTokenRequest($token)->getJson('/api/v1/me')->assertStatus(401);
    }

    /**
     * Ensure password reset flow works against the real DB row.
     */
    public function test_password_reset_flow_updates_db_row(): void
    {
        $user = $this->createUserWithRole(Role::User);
        $resetToken = Password::createToken($user);

        $this->postJson('/api/v1/password/reset', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'Fr3sh!Passw0rd',
            'password_confirmation' => 'Fr3sh!Passw0rd',
        ])->assertStatus(200);

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('Fr3sh!Passw0rd', $fresh->password));
        $this->assertNull($fresh->remember_token);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }
}
