<?php

namespace App\Support;

use App\Events\IngestionFailureDetected;
use App\Jobs\ProcessVulnerabilityIngestionRun;
use App\Models\DeadLetterJob;
use App\Models\IngestionRun;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Throwable;

class QueueFailureRecorder
{
    public function record(string $connection, string $queue, array $payload, string $exception): DeadLetterJob
    {
        $diagnostics = $this->extractDiagnosticsFromPayload($payload);

        $record = DeadLetterJob::query()->create([
            'connection' => $connection,
            'queue' => $queue,
            'job_uuid' => $payload['uuid'] ?? null,
            'job_name' => $payload['displayName'] ?? null,
            'project_id' => $diagnostics['project_id'],
            'ingestion_run_id' => $diagnostics['ingestion_run_id'],
            'snapshot_id' => $diagnostics['snapshot_id'],
            'source' => $diagnostics['source'],
            'attempt' => $diagnostics['attempt'],
            'payload' => $payload,
            'exception' => $exception,
            'failed_at' => now(),
        ]);

        Log::channel('ingestion')->error('ingestion.lifecycle.queue_failure', [
            'stage' => 'failed',
            'correlation_id' => Context::get('correlation_id'),
            'connection' => $connection,
            'queue' => $queue,
            'job_uuid' => $record->job_uuid,
            'dead_letter_id' => $record->id,
            'project_id' => $record->project_id,
            'source' => $record->source,
            'ingestion_run_id' => $record->ingestion_run_id,
            'snapshot_id' => $record->snapshot_id,
            'attempt' => $record->attempt,
        ]);

        if ($record->ingestion_run_id !== null) {
            event(new IngestionFailureDetected($record));
        }

        return $record;
    }

    public function recordFromFailedEvent(JobFailed $event): DeadLetterJob
    {
        return $this->record(
            $event->connectionName,
            $event->job->getQueue(),
            $event->job->payload(),
            $event->exception->getMessage(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   project_id: string|null,
     *   ingestion_run_id: string|null,
     *   snapshot_id: string|null,
     *   source: string|null,
     *   attempt: int|null
     * }
     */
    protected function extractDiagnosticsFromPayload(array $payload): array
    {
        $attempt = isset($payload['attempts']) ? (int) $payload['attempts'] : null;

        $fallback = [
            'project_id' => null,
            'ingestion_run_id' => null,
            'snapshot_id' => null,
            'source' => null,
            'attempt' => $attempt,
        ];

        $commandName = data_get($payload, 'data.commandName');
        $serializedCommand = data_get($payload, 'data.command');

        if ($commandName !== ProcessVulnerabilityIngestionRun::class || ! is_string($serializedCommand)) {
            return $fallback;
        }

        try {
            $command = unserialize($serializedCommand, [
                'allowed_classes' => [ProcessVulnerabilityIngestionRun::class],
            ]);

            if (! $command instanceof ProcessVulnerabilityIngestionRun) {
                return $fallback;
            }

            $ingestionRunId = $command->ingestionRunId;
            $ingestionRun = IngestionRun::query()->find($ingestionRunId);

            return [
                'project_id' => $ingestionRun?->project_id,
                'ingestion_run_id' => $ingestionRunId,
                'snapshot_id' => $ingestionRun?->snapshot_id,
                'source' => $ingestionRun?->source ?? strtolower(trim($command->source)),
                'attempt' => $attempt,
            ];
        } catch (Throwable) {
            return $fallback;
        }
    }
}
