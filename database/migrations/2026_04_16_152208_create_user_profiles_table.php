<?php

use App\Enums\RegionEnum;
use App\Enums\SchoolLevelEnum;
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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('username')->unique();
            $table->enum('level', SchoolLevelEnum::values());
            $table->string('class')->nullable();
            $table->enum('region', RegionEnum::values());
            $table->string('city')->nullable();
            $table->boolean('dark_mode')->default(false);
            $table->boolean('notifications_on')->default(false);
            $table->boolean('onboarding_done')->default(false);
            $table->jsonb('quiz_preferences')->nullable();
            $table->enum('last_level_seen', SchoolLevelEnum::values())->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
