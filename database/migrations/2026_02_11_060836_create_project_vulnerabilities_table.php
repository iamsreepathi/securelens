<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_vulnerabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('osv_url');
            $table->decimal('cvss_score', 4, 1)->nullable();
            $table->string('ecosystem');
            $table->string('package_name');
            $table->string('version');
            $table->string('fixed_version')->nullable();
            $table->string('source');
            $table->timestampTz('ingested_at');
            $table->timestampsTz();

            $table->index(['project_id', 'ecosystem']);
            $table->index(['project_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_vulnerabilities');
    }
};
