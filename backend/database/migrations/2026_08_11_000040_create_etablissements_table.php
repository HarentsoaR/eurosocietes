<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('siret', 14)->unique();
            $table->string('nic', 5);
            $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->boolean('est_siege')->default(false);
            $table->foreignId('activite_naf_id')->nullable()->constrained('activites_naf')->nullOnDelete();
            $table->char('etat_administratif', 1)->default('A');
            $table->char('statut_diffusion', 1)->nullable();
            $table->date('date_creation')->nullable();
            $table->date('date_debut')->nullable();
            $table->string('numero_voie', 10)->nullable();
            $table->string('indice_repetition', 3)->nullable();
            $table->string('type_voie', 10)->nullable();
            $table->string('libelle_voie', 255)->nullable();
            $table->string('complement_adresse')->nullable();
            $table->string('code_postal', 5)->nullable();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->string('libelle_commune', 150)->nullable();
            $table->text('adresse_complete')->nullable();
            $table->string('slug', 180)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['entreprise_id', 'est_siege']);
            $table->index('code_postal');
        });

        DB::statement('ALTER TABLE etablissements ADD CONSTRAINT etablissements_siret_check CHECK (siret ~ \'^[0-9]{14}$\')');
        DB::statement('ALTER TABLE etablissements ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX etablissements_latlng_gist_idx ON etablissements USING GIST (latlng)');
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
