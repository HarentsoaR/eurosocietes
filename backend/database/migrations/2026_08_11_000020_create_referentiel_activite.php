<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites_naf', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('section', 1);
            $table->string('section_libelle', 150)->nullable();
            $table->string('division', 2)->nullable();
            $table->string('division_libelle', 255)->nullable();
            $table->string('groupe', 5)->nullable();
            $table->string('groupe_libelle', 255)->nullable();
            $table->string('classe', 5)->nullable();
            $table->string('classe_libelle', 255)->nullable();
            $table->string('libelle', 255);
            $table->timestamps();
            $table->index('section');
            $table->index('classe');
        });

        Schema::create('specialites', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialites');
        Schema::dropIfExists('activites_naf');
    }
};
