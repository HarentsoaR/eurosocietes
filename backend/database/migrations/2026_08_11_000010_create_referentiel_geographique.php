<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->id();
            $table->string('code_iso2', 2)->unique();
            $table->string('code_iso3', 3)->unique();
            $table->string('code_insee', 5)->unique();
            $table->string('libelle', 150);
            $table->string('slug', 180)->unique();
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('libelle', 150);
            $table->string('slug', 180)->unique();
            $table->foreignId('pays_id')->constrained('pays')->cascadeOnDelete();
            $table->timestamps();
            $table->index('pays_id');
        });

        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('libelle', 150);
            $table->string('slug', 180)->unique();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->timestamps();
            $table->index('region_id');
        });

        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->string('code_insee', 5)->unique();
            $table->string('libelle', 150);
            $table->string('slug', 180)->unique();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->string('arrondissement', 3)->nullable();
            $table->integer('population')->nullable();
            $table->timestamps();
            $table->index('departement_id');
            $table->index('libelle');
        });

        DB::statement('ALTER TABLE villes ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX villes_latlng_gist_idx ON villes USING GIST (latlng)');
        DB::statement('CREATE INDEX villes_libelle_trgm_idx ON villes USING GIN (libelle gin_trgm_ops)');

        Schema::create('ville_code_postal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->string('code_postal', 5);
            $table->unique(['ville_id', 'code_postal']);
            $table->index('code_postal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ville_code_postal');
        Schema::dropIfExists('villes');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('pays');
    }
};
