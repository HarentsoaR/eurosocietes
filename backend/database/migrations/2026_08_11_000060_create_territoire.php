<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quartiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->string('libelle', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('ville_id');
        });

        DB::statement('ALTER TABLE quartiers ADD COLUMN zone geography(Polygon, 4326)');
        DB::statement('CREATE INDEX quartiers_zone_gist_idx ON quartiers USING GIST (zone)');
        DB::statement('ALTER TABLE quartiers ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX quartiers_latlng_gist_idx ON quartiers USING GIST (latlng)');

        Schema::create('monuments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->foreignId('quartier_id')->nullable()->constrained('quartiers')->nullOnDelete();
            $table->string('libelle', 255);
            $table->string('slug', 180)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('ref_merimee', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('adresse')->nullable();
            $table->timestamps();

            $table->index('ville_id');
        });

        DB::statement('ALTER TABLE monuments ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX monuments_latlng_gist_idx ON monuments USING GIST (latlng)');

        Schema::create('espaces_verts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->foreignId('quartier_id')->nullable()->constrained('quartiers')->nullOnDelete();
            $table->string('libelle', 255);
            $table->string('type', 100)->nullable();
            $table->timestamps();

            $table->index('ville_id');
        });

        DB::statement('ALTER TABLE espaces_verts ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX espaces_verts_latlng_gist_idx ON espaces_verts USING GIST (latlng)');
    }

    public function down(): void
    {
        Schema::dropIfExists('espaces_verts');
        Schema::dropIfExists('monuments');
        Schema::dropIfExists('quartiers');
    }
};
