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
        Schema::table('dead_letter_jobs', function (Blueprint $table) {
            $table->foreignUuid('project_id')->nullable()->after('job_name')->constrained('projects')->nullOnDelete();
            $table->uuid('ingestion_run_id')->nullable()->after('project_id');
            $table->uuid('snapshot_id')->nullable()->after('ingestion_run_id');
            $table->string('source')->nullable()->after('snapshot_id');
            $table->unsignedInteger('attempt')->nullable()->after('source');

            $table->index(['project_id', 'source', 'failed_at']);
            $table->index('ingestion_run_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dead_letter_jobs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'source', 'failed_at']);
            $table->dropIndex(['ingestion_run_id']);

            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'ingestion_run_id',
                'snapshot_id',
                'source',
                'attempt',
            ]);
        });
    }
};
