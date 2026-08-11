<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('siren', 9)->unique();
            $table->string('denomination')->nullable();
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->string('sigle', 50)->nullable();
            $table->string('enseigne')->nullable();
            $table->string('categorie_juridique', 10)->nullable();
            $table->string('categorie_entreprise', 10)->nullable();
            $table->string('tranche_effectifs', 10)->nullable();
            $table->unsignedSmallInteger('annee_effectifs')->nullable();
            $table->boolean('caractere_employeur')->nullable();
            $table->char('etat_administratif', 1)->default('A');
            $table->char('statut_diffusion', 1)->nullable();
            $table->date('date_creation')->nullable();
            $table->date('date_radiation')->nullable();
            $table->date('date_debut_activite')->nullable();
            $table->foreignId('activite_naf_id')->nullable()->constrained('activites_naf')->nullOnDelete();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->text('adresse_complete')->nullable();
            $table->string('slug', 180)->nullable()->unique();
            $table->boolean('allow_public_contacts')->default(false);
            $table->boolean('visible')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ville_id', 'activite_naf_id']);
            $table->index(['etat_administratif', 'visible']);
        });

        DB::statement('ALTER TABLE entreprises ADD CONSTRAINT entreprises_siren_check CHECK (siren ~ \'^[0-9]{9}$\')');

        DB::statement('ALTER TABLE entreprises ADD COLUMN latlng geography(Point, 4326)');
        DB::statement('CREATE INDEX entreprises_latlng_gist_idx ON entreprises USING GIST (latlng)');
        DB::statement('CREATE INDEX entreprises_denomination_trgm_idx ON entreprises USING GIN (denomination gin_trgm_ops)');
        DB::statement('CREATE INDEX entreprises_nom_trgm_idx ON entreprises USING GIN (nom gin_trgm_ops)');
        DB::statement('CREATE INDEX entreprises_slug_trgm_idx ON entreprises USING GIN (slug gin_trgm_ops)');

        DB::statement('ALTER TABLE entreprises ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX entreprises_search_vector_idx ON entreprises USING GIN (search_vector)');
        DB::statement("CREATE OR REPLACE FUNCTION entreprises_search_vector_update() RETURNS trigger AS \$\$
BEGIN
  NEW.search_vector :=
    setweight(to_tsvector('french', coalesce(NEW.denomination, '')), 'A') ||
    setweight(to_tsvector('french', coalesce(NEW.nom, '')), 'A') ||
    setweight(to_tsvector('french', coalesce(NEW.enseigne, '')), 'B') ||
    setweight(to_tsvector('french', coalesce(NEW.adresse_complete, '')), 'C');
  RETURN NEW;
END \$\$ LANGUAGE plpgsql");
        DB::statement('CREATE TRIGGER entreprises_search_vector_trg BEFORE INSERT OR UPDATE OF denomination, nom, enseigne, adresse_complete ON entreprises FOR EACH ROW EXECUTE FUNCTION entreprises_search_vector_update()');

        Schema::create('entreprise_specialite', function (Blueprint $table) {
            $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->foreignId('specialite_id')->constrained('specialites')->cascadeOnDelete();
            $table->primary(['entreprise_id', 'specialite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprise_specialite');
        DB::statement('DROP TRIGGER IF EXISTS entreprises_search_vector_trg ON entreprises');
        DB::statement('DROP FUNCTION IF EXISTS entreprises_search_vector_update()');
        Schema::dropIfExists('entreprises');
    }
};
