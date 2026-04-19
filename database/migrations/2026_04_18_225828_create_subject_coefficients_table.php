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
        Schema::create('subject_coefficients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('serie_id')->constrained()->cascadeOnDelete();
            $table->string('subject_name');
            $table->integer('coefficient');
            $table->decimal('minimum_grade', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['serie_id', 'subject_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_coefficients');
    }
};
