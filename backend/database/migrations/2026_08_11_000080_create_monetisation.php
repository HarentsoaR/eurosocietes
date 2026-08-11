<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('plan', 50);
            $table->string('statut', 20)->default('actif');
            $table->string('stripe_id', 100)->nullable()->unique();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->boolean('renouvellement_auto')->default(true);
            $table->timestamps();

            $table->index(['entreprise_id', 'statut']);
        });

        DB::statement('ALTER TABLE abonnements ADD CONSTRAINT abonnements_dates_check CHECK (date_fin IS NULL OR date_debut <= date_fin)');

        Schema::create('publicites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->string('emplacement', 50);
            $table->string('url_cible', 500)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->unsignedBigInteger('budget')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clics')->default(0);
            $table->timestamps();

            $table->index(['emplacement', 'statut']);
        });

        DB::statement('ALTER TABLE publicites ADD CONSTRAINT publicites_dates_check CHECK (date_fin IS NULL OR date_debut <= date_fin)');
    }

    public function down(): void
    {
        Schema::dropIfExists('publicites');
        Schema::dropIfExists('abonnements');
    }
};
