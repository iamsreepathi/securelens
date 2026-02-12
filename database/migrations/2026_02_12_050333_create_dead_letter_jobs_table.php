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
        Schema::create('dead_letter_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('connection');
            $table->string('queue');
            $table->string('job_uuid')->nullable()->index();
            $table->string('job_name')->nullable();
            $table->json('payload');
            $table->longText('exception');
            $table->timestampTz('failed_at');
            $table->timestampsTz();

            $table->index(['connection', 'queue']);
            $table->index('failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dead_letter_jobs');
    }
};
