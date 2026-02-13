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
        Schema::table('project_vulnerabilities', function (Blueprint $table) {
            $table->index(['project_id', 'cvss_score'], 'project_vuln_project_cvss_idx');
            $table->index(['project_id', 'fixed_version'], 'project_vuln_project_fix_idx');
            $table->index(['project_id', 'package_name'], 'project_vuln_project_package_idx');
            $table->index(['project_id', 'ingested_at'], 'project_vuln_project_ingested_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_vulnerabilities', function (Blueprint $table) {
            $table->dropIndex('project_vuln_project_cvss_idx');
            $table->dropIndex('project_vuln_project_fix_idx');
            $table->dropIndex('project_vuln_project_package_idx');
            $table->dropIndex('project_vuln_project_ingested_idx');
        });
    }
};
