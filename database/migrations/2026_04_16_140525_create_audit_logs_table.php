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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('action', 50);
            $table->string('model_type', 100);
            $table->string('model_id', 36);
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['model_type', 'model_id'], 'idx_audit_logs_model');

            // "Montre-moi toutes les actions de cet admin"
            // Requête : WHERE user_id = 'uuid'
            $table->index('user_id', 'idx_audit_logs_user');

            // "Montre-moi toutes les suppressions récentes"
            // Requête : WHERE action = 'deleted' ORDER BY created_at DESC
            $table->index('action', 'idx_audit_logs_action');

            // "Montre-moi les actions entre le 1er et le 10 avril"
            // Requête : WHERE created_at BETWEEN '2026-04-01' AND '2026-04-10'
            $table->index('created_at', 'idx_audit_logs_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
