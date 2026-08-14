<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class ApiFoundationTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_list_envelope_has_data_and_meta(): void
    {
        $response = $this->getJson('/api/v1/naf-activites');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['page', 'per_page', 'total', 'last_page']]);
    }

    public function test_unknown_route_returns_json_404(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_ping_endpoint_returns_pong(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertJsonPath('message', 'pong');
    }

    public function test_unauthenticated_write_returns_401(): void
    {
        $this->postJson('/api/v1/entreprises', [])
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}
