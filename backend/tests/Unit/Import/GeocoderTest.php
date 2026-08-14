<?php

namespace Tests\Unit\Import;

use App\Import\Geocoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeocoderTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordonnees_commune_sans_ville_retourne_null(): void
    {
        $geocoder = new Geocoder;
        $this->assertNull($geocoder->coordonneesCommune(999999));
    }
}
