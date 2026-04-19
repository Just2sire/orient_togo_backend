<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // field_serie (Alphabetical order: field, serie)
        Schema::create('field_serie', function (Blueprint $table) {
            $table->foreignUuid('field_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('serie_id')->constrained()->cascadeOnDelete();
            $table->primary(['field_id', 'serie_id']);
        });

        // establishment_field (Alphabetical order: establishment, field)
        Schema::create('establishment_field', function (Blueprint $table) {
            $table->foreignUuid('establishment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('field_id')->constrained()->cascadeOnDelete();
            $table->primary(['establishment_id', 'field_id']);
        });

        // establishment_serie (Alphabetical order: establishment, serie)
        Schema::create('establishment_serie', function (Blueprint $table) {
            $table->foreignUuid('establishment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('serie_id')->constrained()->cascadeOnDelete();
            $table->primary(['establishment_id', 'serie_id']);
        });

        // career_field (Alphabetical order: career, field)
        Schema::create('career_field', function (Blueprint $table) {
            $table->foreignUuid('career_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('field_id')->constrained()->cascadeOnDelete();
            $table->primary(['career_id', 'field_id']);
        });

        // field_growth_sector (Alphabetical order: field, growth_sector)
        Schema::create('field_growth_sector', function (Blueprint $table) {
            $table->foreignUuid('field_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('growth_sector_id')->constrained()->cascadeOnDelete();
            $table->primary(['field_id', 'growth_sector_id']);
        });

        // career_course (Alphabetical order: career, course)
        Schema::create('career_course', function (Blueprint $table) {
            $table->foreignUuid('career_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained()->cascadeOnDelete();
            $table->primary(['career_id', 'course_id']);
        });

        // taggables (Polymorphic)
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignUuid('tag_id')->constrained()->cascadeOnDelete();
            $table->uuid('taggable_id');
            $table->string('taggable_type');
            $table->index(['taggable_id', 'taggable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('career_course');
        Schema::dropIfExists('field_growth_sector');
        Schema::dropIfExists('career_field');
        Schema::dropIfExists('establishment_serie');
        Schema::dropIfExists('establishment_field');
        Schema::dropIfExists('field_serie');
    }
};
