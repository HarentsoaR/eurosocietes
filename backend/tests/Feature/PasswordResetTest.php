<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ApiResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        Notification::fake();
    }

    public function test_forgot_password_sends_reset_notification_for_existing_email(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'reset@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', __('passwords.sent'));

        Notification::assertSentTo($user, ApiResetPasswordNotification::class);
    }

    public function test_forgot_password_does_not_leak_whether_email_exists(): void
    {
        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', __('passwords.sent'));

        Notification::assertNothingSent();
    }

    public function test_reset_password_changes_password_and_revokes_tokens(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = $user->createToken('old-session')->plainTextToken;
        $resetToken = Password::createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $resetToken,
            'email' => 'reset@example.com',
            'password' => 'N3w!Str0ngPassw0rd',
            'password_confirmation' => 'N3w!Str0ngPassw0rd',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', __('passwords.reset'));

        $this->assertTrue(
            Hash::check('N3w!Str0ngPassw0rd', $user->fresh()->password)
        );

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => 'invalid-token',
            'email' => 'reset@example.com',
            'password' => 'N3w!Str0ngPassw0rd',
            'password_confirmation' => 'N3w!Str0ngPassw0rd',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', __('passwords.failed'));
    }

    public function test_reset_password_validates_input(): void
    {
        $response = $this->postJson('/api/v1/password/reset', [
            'token' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }
}
