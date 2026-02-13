<x-layouts::app :title="__('Admin Dashboard')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Admin Operations Dashboard') }}</flux:heading>
            <flux:text>{{ __('Cross-tenant health, ingestion reliability trends, and queue pressure indicators.') }}</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Teams') }}</flux:text>
                <flux:heading size="lg" data-test="teams-total">{{ $tenantSummary['teams_total'] }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Projects') }}</flux:text>
                <flux:heading size="lg" data-test="projects-total">{{ $tenantSummary['projects_total'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-600 dark:text-zinc-300">{{ __('Active: :count', ['count' => $tenantSummary['projects_active']]) }}</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Users') }}</flux:text>
                <flux:heading size="lg" data-test="users-total">{{ $tenantSummary['users_total'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-600 dark:text-zinc-300">{{ __('With team access: :count', ['count' => $tenantSummary['users_with_team_access']]) }}</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Ingestion Runs') }}</flux:text>
                <flux:heading size="lg" data-test="runs-total">{{ $ingestionSummary['runs_total'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-600 dark:text-zinc-300">{{ __('Processed: :count', ['count' => $ingestionSummary['runs_processed']]) }}</flux:text>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Queue Backlog') }}</flux:text>
                <flux:heading size="lg" data-test="pending-jobs-total">{{ $queueHealth['pending_jobs_total'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-600 dark:text-zinc-300">{{ __('Retries: :count', ['count' => $queueHealth['retrying_jobs_total']]) }}</flux:text>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:heading size="lg">{{ __('Ingestion Trend (7 days)') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Successes are processed runs. Failures are dead-letter ingestion jobs.') }}</flux:text>

                <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700/70">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/85">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Day') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Successes') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Failures') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900/55">
                            @foreach ($ingestionTrend as $trend)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $trend['label'] }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200" data-test="trend-success-{{ $trend['date'] }}">{{ $trend['success_count'] }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200" data-test="trend-failure-{{ $trend['date'] }}">{{ $trend['failure_count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm shadow-zinc-200/60 dark:border-zinc-700/70 dark:bg-zinc-900/60 dark:shadow-zinc-950/35">
                <flux:heading size="lg">{{ __('Queue Health') }}</flux:heading>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700/70 dark:bg-zinc-900/60">
                        <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Ingestion Queue Depth') }}</flux:text>
                        <flux:heading size="lg" data-test="ingestion-queue-depth">{{ $queueHealth['ingestion_queue_depth'] }}</flux:heading>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700/70 dark:bg-zinc-900/60">
                        <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Delayed Jobs') }}</flux:text>
                        <flux:heading size="lg" data-test="delayed-jobs-total">{{ $queueHealth['delayed_jobs_total'] }}</flux:heading>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700/70 dark:bg-zinc-900/60">
                        <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Failed Jobs (24h)') }}</flux:text>
                        <flux:heading size="lg" data-test="failed-jobs-last-24h">{{ $queueHealth['failed_jobs_last_24h'] }}</flux:heading>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700/70 dark:bg-zinc-900/60">
                        <flux:text class="text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Dead Letters (24h)') }}</flux:text>
                        <flux:heading size="lg" data-test="dead-letters-last-24h">{{ $queueHealth['dead_letters_last_24h'] }}</flux:heading>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700/70">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/85">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Queue') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Depth') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-200">{{ __('Max Attempts') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900/55">
                            @forelse ($queueHealth['queue_depths'] as $queue)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $queue['queue'] }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">{{ $queue['depth'] }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200">{{ $queue['max_attempts'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-sm text-zinc-700 dark:text-zinc-200">{{ __('No queued jobs pending at this time.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
