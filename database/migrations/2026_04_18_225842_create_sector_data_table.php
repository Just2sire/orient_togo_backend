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
        Schema::create('sector_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('career_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('growth_sector_id')->nullable()->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->integer('average_salary')->nullable();
            $table->decimal('employment_rate', 5, 2)->nullable();
            $table->integer('offers_per_year')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sector_data');
    }
};
