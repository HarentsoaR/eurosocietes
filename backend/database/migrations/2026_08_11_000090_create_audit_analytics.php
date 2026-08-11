<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('source', 255)->nullable();
            $table->string('fichier', 500)->nullable();
            $table->string('statut', 20)->default('pending');
            $table->unsignedBigInteger('lignes_total')->default(0);
            $table->unsignedBigInteger('lignes_traitees')->default(0);
            $table->unsignedBigInteger('lignes_inserees')->default(0);
            $table->unsignedBigInteger('lignes_maj')->default(0);
            $table->unsignedBigInteger('lignes_radiees')->default(0);
            $table->unsignedBigInteger('lignes_erreur')->default(0);
            $table->jsonb('resume_state')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'statut']);
        });

        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->string('niveau', 10);
            $table->text('message');
            $table->string('siren', 9)->nullable();
            $table->string('siret', 14)->nullable();
            $table->unsignedBigInteger('ligne')->nullable();
            $table->jsonb('context')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['import_id', 'niveau']);
        });

        Schema::create('historique', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50);
            $table->jsonb('avant')->nullable();
            $table->jsonb('apres')->nullable();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('import_id')->nullable()->constrained('imports')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id', 'created_at']);
        });

        Schema::create('statistiques', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->date('periode');
            $table->unsignedBigInteger('compteur')->default(0);
            $table->timestamps();

            $table->unique(['type', 'entity_type', 'entity_id', 'periode']);
            $table->index(['type', 'periode']);
        });

        Schema::create('recherches', function (Blueprint $table) {
            $table->id();
            $table->string('terme', 255);
            $table->unsignedInteger('nb_resultats')->default(0);
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('terme');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recherches');
        Schema::dropIfExists('statistiques');
        Schema::dropIfExists('import_logs');
        Schema::dropIfExists('imports');
        Schema::dropIfExists('historique');
    }
};
