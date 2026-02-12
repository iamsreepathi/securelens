<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ProcessQueuedTask implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $maxExceptions = 3;

    public function __construct(
        public bool $shouldFail = false,
    ) {
        $this->tries = (int) config('queue.worker.tries', 5);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        /** @var array<int, int> $backoff */
        $backoff = config('queue.worker.backoff', [1, 5, 10]);

        return $backoff;
    }

    public function handle(): void
    {
        Log::channel('ingestion')->info('ingestion.lifecycle.process_queued_task', [
            'stage' => 'started',
            'correlation_id' => Context::get('correlation_id'),
        ]);

        if ($this->shouldFail) {
            Log::channel('ingestion')->error('ingestion.lifecycle.process_queued_task', [
                'stage' => 'failed',
                'correlation_id' => Context::get('correlation_id'),
            ]);

            throw new RuntimeException('Simulated queued task failure');
        }

        Log::channel('ingestion')->info('ingestion.lifecycle.process_queued_task', [
            'stage' => 'completed',
            'correlation_id' => Context::get('correlation_id'),
        ]);
    }
}
