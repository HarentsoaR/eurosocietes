<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRoles;

class HardeningTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_public_get_has_cache_control_headers(): void
    {
        $this->getJson('/api/v1/naf-activites')
            ->assertHeader('Cache-Control', 'max-age=600, public');
    }

    public function test_unauthenticated_write_returns_json_401(): void
    {
        $this->postJson('/api/v1/entreprises', [])
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_validation_error_has_errors_key(): void
    {
        $this->getJson('/api/v1/search')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_model_not_found_returns_json_404(): void
    {
        $this->getJson('/api/v1/entreprises/999999999')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }
}
