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
        Schema::create('ingestion_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('source');
            $table->uuid('snapshot_id');
            $table->string('payload_hash', 64);
            $table->timestampTz('ingested_at');
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['project_id', 'source', 'snapshot_id']);
            $table->index(['project_id', 'source', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingestion_runs');
    }
};
