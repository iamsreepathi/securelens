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
        Schema::create('project_webhook_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('rotated_from_id')->nullable();
            $table->string('name');
            $table->string('token_prefix', 20);
            $table->string('token_hash', 64)->unique();
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();

            $table->index(['project_id', 'revoked_at']);
            $table->index(['project_id', 'token_prefix']);
        });

        Schema::table('project_webhook_tokens', function (Blueprint $table): void {
            $table->foreign('rotated_from_id')
                ->references('id')
                ->on('project_webhook_tokens')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_webhook_tokens');
    }
};
