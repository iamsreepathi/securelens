<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOperationalActionController;
use App\Http\Controllers\Admin\AdminRunbookController;
use App\Http\Controllers\Admin\IngestionFailureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTeamAssignmentController;
use App\Http\Controllers\ProjectWebhookTokenController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\VulnerabilityController;
use App\Support\HealthChecks;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/health', function (HealthChecks $healthChecks) {
    $report = $healthChecks->report();
    $statusCode = $report['status'] === 'ok' ? 200 : 503;

    return response()->json($report, $statusCode);
})->name('health');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('teams', TeamController::class);
    Route::resource('projects', ProjectController::class);
    Route::get('vulnerabilities', [VulnerabilityController::class, 'index'])->name('vulnerabilities.index');
    Route::get('vulnerabilities/{vulnerability}', [VulnerabilityController::class, 'show'])->name('vulnerabilities.show');
    Route::post('projects/{project}/webhook-tokens', [ProjectWebhookTokenController::class, 'store'])->name('projects.webhook-tokens.store');
    Route::post('projects/{project}/webhook-tokens/{webhookToken}/rotate', [ProjectWebhookTokenController::class, 'rotate'])->name('projects.webhook-tokens.rotate');
    Route::delete('projects/{project}/webhook-tokens/{webhookToken}', [ProjectWebhookTokenController::class, 'destroy'])->name('projects.webhook-tokens.destroy');
    Route::post('projects/{project}/teams', [ProjectTeamAssignmentController::class, 'store'])->name('projects.teams.store');
    Route::delete('projects/{project}/teams/{team}', [ProjectTeamAssignmentController::class, 'destroy'])->name('projects.teams.destroy');
    Route::post('teams/{team}/members', [TeamMemberController::class, 'store'])->name('teams.members.store');
    Route::put('teams/{team}/members/{member}', [TeamMemberController::class, 'update'])->name('teams.members.update');
    Route::delete('teams/{team}/members/{member}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function (): void {
    Route::get('admin', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('admin/ingestion-failures', [IngestionFailureController::class, 'index'])
        ->name('admin.ingestion-failures.index');
    Route::get('admin/audit-logs', [AdminAuditLogController::class, 'index'])
        ->name('admin.audit-logs.index');
    Route::get('admin/runbooks', [AdminRunbookController::class, 'index'])
        ->name('admin.runbooks.index');
    Route::post('admin/ingestion-failures/{failure}/retry', [AdminOperationalActionController::class, 'retryDeadLetterJob'])
        ->name('admin.ingestion-failures.retry');
    Route::post('admin/projects/{project}/webhook-tokens/disable', [AdminOperationalActionController::class, 'disableProjectWebhookTokens'])
        ->name('admin.projects.webhook-tokens.disable');
});

require __DIR__.'/settings.php';
