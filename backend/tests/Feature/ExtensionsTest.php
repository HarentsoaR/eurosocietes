<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExtensionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgis_pg_trgm_btree_gist_unaccent_sont_actives(): void
    {
        $extensions = collect(DB::select("SELECT extname FROM pg_extension"))
            ->pluck('extname')
            ->all();

        $this->assertContains('postgis', $extensions);
        $this->assertContains('pg_trgm', $extensions);
        $this->assertContains('btree_gist', $extensions);
        $this->assertContains('unaccent', $extensions);
    }
}
