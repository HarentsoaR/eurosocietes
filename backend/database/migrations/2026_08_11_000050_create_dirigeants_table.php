<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dirigeants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->cascadeOnDelete();
            $table->string('qualite', 150)->nullable();
            $table->string('nom', 150)->nullable();
            $table->string('prenoms', 255)->nullable();
            $table->date('date_debut_fonction')->nullable();
            $table->boolean('est_principal')->default(false);
            $table->timestamps();

            $table->index('entreprise_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dirigeants');
    }
};
