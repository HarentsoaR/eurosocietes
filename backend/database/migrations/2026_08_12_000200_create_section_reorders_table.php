<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_reorders', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->unique(['entity_type', 'entity_id', 'section_id'], 'section_reorders_entity_section_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_reorders');
    }
};