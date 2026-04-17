<?php

use App\Enums\OtpTypeEnum;
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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 20)->index();
            $table->string('code');
            $table->enum('type', OtpTypeEnum::values());
            $table->tinyInteger('attempts')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
