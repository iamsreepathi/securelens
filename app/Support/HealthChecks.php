<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthChecks
{
    /**
     * @return array{
     *   status: string,
     *   checks: array{
     *     app: array{status: string},
     *     db: array{status: string, error: string|null},
     *     queue: array{
     *       status: string,
     *       connection: string,
     *       failed_driver: string|null,
     *       dead_letter_table_exists: bool
     *     }
     *   }
     * }
     */
    public function report(): array
    {
        $checks = [
            'app' => ['status' => 'ok'],
            'db' => $this->databaseCheck(),
            'queue' => $this->queueCheck(),
        ];

        $overallStatus = collect($checks)->every(fn (array $check): bool => $check['status'] === 'ok')
            ? 'ok'
            : 'degraded';

        return [
            'status' => $overallStatus,
            'checks' => $checks,
        ];
    }

    /**
     * @return array{status: string, error: string|null}
     */
    protected function databaseCheck(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'ok', 'error' => null];
        } catch (Throwable $throwable) {
            return [
                'status' => 'fail',
                'error' => $throwable->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: string, connection: string, failed_driver: string|null, dead_letter_table_exists: bool}
     */
    protected function queueCheck(): array
    {
        $connection = (string) config('queue.default');
        $failedDriver = config('queue.failed.driver');
        $deadLetterTable = (string) config('queue.dead_letter.table', 'dead_letter_jobs');
        $deadLetterTableExists = Schema::hasTable($deadLetterTable);
        $connectionConfigured = is_array(config("queue.connections.{$connection}"));
        $status = $connectionConfigured && $failedDriver !== 'null' && $deadLetterTableExists ? 'ok' : 'fail';

        return [
            'status' => $status,
            'connection' => $connection,
            'failed_driver' => is_string($failedDriver) ? $failedDriver : null,
            'dead_letter_table_exists' => $deadLetterTableExists,
        ];
    }
}
