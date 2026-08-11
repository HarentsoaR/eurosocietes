<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * @var array<string, string>
     */
    private array $credentials = [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_register_creates_user_with_default_role(): void
    {
        $response = $this->postJson('/api/v1/register', $this->credentials);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'jean@example.com')
            ->assertJsonPath('data.roles.0.key', Role::User->value);

        $this->assertDatabaseHas('utilisateurs', ['email' => 'jean@example.com']);
        $user = User::where('email', 'jean@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::User));
        $this->assertNotSame($this->credentials['password'], $user->password);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Jean',
            'email' => 'jean2@example.com',
            'password' => 'Str0ng!Passw0rd',
            'password_confirmation' => 'Different!Passw0rd',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $this->postJson('/api/v1/register', $this->credentials);
        $response = $this->postJson('/api/v1/register', $this->credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_issues_bearer_token(): void
    {
        $this->postJson('/api/v1/register', $this->credentials);

        $response = $this->postJson('/api/v1/login', [
            'email' => $this->credentials['email'],
            'password' => $this->credentials['password'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $token = $response->json('data.token');
        $this->assertIsString($token);
        $this->assertStringContainsString('|', $token);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->postJson('/api/v1/register', $this->credentials);

        $response = $this->postJson('/api/v1/login', [
            'email' => $this->credentials['email'],
            'password' => 'Wrong!Passw0rd',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::User);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::User);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);

        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/me')->assertStatus(401);
    }
}
