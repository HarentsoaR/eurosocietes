<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_client_request_id_is_echoed_back(): void
    {
        $this->getJson('/api/v1/ping', ['X-Request-ID' => 'client-trace-id-42'])
            ->assertStatus(200)
            ->assertHeader('X-Request-ID', 'client-trace-id-42');
    }
}
