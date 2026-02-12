<?php

namespace App\Support;

use App\Models\DeadLetterJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class QueueFailureRecorder
{
    public function record(string $connection, string $queue, array $payload, string $exception): DeadLetterJob
    {
        $record = DeadLetterJob::query()->create([
            'connection' => $connection,
            'queue' => $queue,
            'job_uuid' => $payload['uuid'] ?? null,
            'job_name' => $payload['displayName'] ?? null,
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
        ]);

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
}
