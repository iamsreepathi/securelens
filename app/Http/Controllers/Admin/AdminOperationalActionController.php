<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisableProjectWebhookTokensRequest;
use App\Http\Requests\RetryDeadLetterJobRequest;
use App\Jobs\ProcessVulnerabilityIngestionRun;
use App\Models\DeadLetterJob;
use App\Models\Project;
use App\Support\AdminOperationalActionRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminOperationalActionController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function retryDeadLetterJob(
        RetryDeadLetterJobRequest $request,
        DeadLetterJob $failure,
        AdminOperationalActionRecorder $actionRecorder,
    ): RedirectResponse {
        $payload = $this->resolveRetryPayload($failure);

        if ($payload === null) {
            throw ValidationException::withMessages([
                'retry' => 'The selected dead-letter payload cannot be safely retried.',
            ]);
        }

        ProcessVulnerabilityIngestionRun::dispatch(
            ingestionRunId: $payload['ingestion_run_id'],
            source: $payload['source'],
            ingestedAt: $payload['ingested_at'],
            vulnerabilities: $payload['vulnerabilities'],
        )->onQueue($failure->queue);

        $actionRecorder->record(
            actor: $request->user(),
            action: 'retry_dead_letter_job',
            targetType: 'dead_letter_job',
            targetId: (string) $failure->getKey(),
            metadata: [
                'reason' => $request->string('reason')->toString(),
                'project_id' => $failure->project_id,
                'ingestion_run_id' => $payload['ingestion_run_id'],
                'source' => $payload['source'],
                'snapshot_id' => $failure->snapshot_id,
                'attempt' => $failure->attempt,
                'queue' => $failure->queue,
            ],
            beforeState: [
                'dead_letter_id' => $failure->id,
                'job_name' => $failure->job_name,
                'attempt' => $failure->attempt,
                'failed_at' => $failure->failed_at?->toIso8601String(),
            ],
            afterState: [
                'requeued' => true,
                'queue' => $failure->queue,
            ],
        );

        return back()->with('status', 'Retry was queued for dead-letter entry '.$failure->id.'.');
    }

    public function disableProjectWebhookTokens(
        DisableProjectWebhookTokensRequest $request,
        Project $project,
        AdminOperationalActionRecorder $actionRecorder,
    ): RedirectResponse {
        $activeTokenIds = $project->webhookTokens()
            ->whereNull('revoked_at')
            ->pluck('id')
            ->values()
            ->all();

        $activeTokenCount = count($activeTokenIds);

        $revokedCount = $project->webhookTokens()
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);

        $actionRecorder->record(
            actor: $request->user(),
            action: 'disable_project_webhook_tokens',
            targetType: 'project',
            targetId: (string) $project->getKey(),
            metadata: [
                'reason' => $request->string('reason')->toString(),
                'revoked_count' => $revokedCount,
                'revoked_token_ids' => $activeTokenIds,
            ],
            beforeState: [
                'active_token_count' => $activeTokenCount,
                'active_token_ids' => $activeTokenIds,
            ],
            afterState: [
                'active_token_count' => max(0, $activeTokenCount - $revokedCount),
                'revoked_count' => $revokedCount,
            ],
        );

        return back()->with('status', 'Revoked '.$revokedCount.' active token(s) for project '.$project->name.'.');
    }

    /**
     * @return array{
     *   ingestion_run_id: string,
     *   source: string,
     *   ingested_at: string,
     *   vulnerabilities: array<int, array<string, mixed>>
     * }|null
     */
    protected function resolveRetryPayload(DeadLetterJob $failure): ?array
    {
        $commandName = Arr::get($failure->payload, 'data.commandName');
        $serializedCommand = Arr::get($failure->payload, 'data.command');

        if ($commandName !== ProcessVulnerabilityIngestionRun::class || ! is_string($serializedCommand)) {
            return null;
        }

        try {
            $command = unserialize($serializedCommand, [
                'allowed_classes' => [ProcessVulnerabilityIngestionRun::class],
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $command instanceof ProcessVulnerabilityIngestionRun) {
            return null;
        }

        return [
            'ingestion_run_id' => $command->ingestionRunId,
            'source' => $command->source,
            'ingested_at' => $command->ingestedAt,
            'vulnerabilities' => $command->vulnerabilities,
        ];
    }
}
