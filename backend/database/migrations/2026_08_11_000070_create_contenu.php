<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->text('question');
            $table->text('reponse');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('contenus_ia', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('type_contenu', 50);
            $table->text('contenu')->nullable();
            $table->string('statut', 20)->default('pending');
            $table->string('modele', 100)->nullable();
            $table->string('prompt_version', 20)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'type_contenu']);
            $table->index('statut');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('type', 50);
            $table->string('titre')->nullable();
            $table->string('chemin', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('statut_validation', 20)->default('en_attente');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('passeports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->unique()->constrained('entreprises')->cascadeOnDelete();
            $table->string('statut', 20)->default('non_soumis');
            $table->unsignedSmallInteger('score_confidence')->default(0);
            $table->jsonb('badges')->default('[]');
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX passeports_badges_gin_idx ON passeports USING GIN (badges)');
    }

    public function down(): void
    {
        Schema::dropIfExists('passeports');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('contenus_ia');
        Schema::dropIfExists('faq');
    }
};
