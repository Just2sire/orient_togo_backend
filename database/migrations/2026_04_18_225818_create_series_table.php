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
        Schema::create('series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('minimum_average', 4, 2)->default(10.00);
            $table->text('required_profile')->nullable();
            $table->text('after_bac')->nullable();
            $table->jsonb('tips')->nullable();
            $table->boolean('is_active')->default(true);
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
