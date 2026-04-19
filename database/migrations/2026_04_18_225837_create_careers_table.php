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
        Schema::create('careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->jsonb('required_skills')->nullable();
            $table->tinyInteger('market_demand')->default(5); // 1-10
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->text('long_description')->nullable();
            $table->boolean('is_promising')->default(false);
            $table->foreignUuid('growth_sector_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
