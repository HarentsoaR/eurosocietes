<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_api_route_returns_clean_404_json(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Route introuvable.')
            ->assertJsonMissing(['exception', 'trace']);
    }

    public function test_internal_server_error_hides_internals_in_production(): void
    {
        Route::get('/api/v1/tmp-boom', function () {
            throw new RuntimeException('secret internal detail');
        });

        $response = $this->getJson('/api/v1/tmp-boom');

        $response->assertStatus(500)
            ->assertJsonPath('message', 'Une erreur interne est survenue.')
            ->assertJsonMissing(['exception', 'trace']);
    }

    public function test_unknown_web_route_returns_html_404(): void
    {
        $response = $this->get('/does-not-exist');

        $response->assertStatus(404);
    }

    public function test_unauthorized_api_request_returns_401_json(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Non authentifié.');
    }

    public function test_validation_errors_are_structured_json(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_response_includes_request_id_header(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertStatus(200)
            ->assertHeader('X-Request-ID');
    }

    public function test_valid_client_request_id_is_echoed_back(): void
    {
        $id = '7f4e7a2c-1b3d-4c5e-9a6f-0d8e1f2a3b4c';

        $this->getJson('/api/v1/ping', ['X-Request-ID' => $id])
            ->assertStatus(200)
            ->assertHeader('X-Request-ID', $id);
    }

    public function test_invalid_client_request_id_is_replaced_with_generated_uuid(): void
    {
        $response = $this->getJson('/api/v1/ping', ['X-Request-ID' => "malicious\ninjected"]);

        $response->assertStatus(200);

        $requestId = $response->headers->get('X-Request-ID');
        $this->assertNotSame("malicious\ninjected", $requestId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $requestId
        );
    }
}
