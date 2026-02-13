<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Security Overview') }}</flux:heading>
            <flux:text>{{ __('Project-level vulnerability posture, ingestion freshness, and ecosystem spread.') }}</flux:text>
        </div>

        @if (! $hasProjects)
            <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Connect your first project') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Create a project and configure your vulnerability ingestion integration to start seeing risk summaries.') }}
                </flux:text>
                <div class="mt-4">
                    <flux:button :href="route('projects.create')" variant="primary" wire:navigate>
                        {{ __('Create Project') }}
                    </flux:button>
                </div>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30">
                    <flux:text class="text-xs uppercase tracking-wide text-red-700 dark:text-red-300">{{ __('Critical') }}</flux:text>
                    <flux:heading size="xl" data-test="critical-count">{{ $summary['critical'] }}</flux:heading>
                </div>
                <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/50 dark:bg-orange-950/30">
                    <flux:text class="text-xs uppercase tracking-wide text-orange-700 dark:text-orange-300">{{ __('High') }}</flux:text>
                    <flux:heading size="xl" data-test="high-count">{{ $summary['high'] }}</flux:heading>
                </div>
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/50 dark:bg-yellow-950/30">
                    <flux:text class="text-xs uppercase tracking-wide text-yellow-700 dark:text-yellow-300">{{ __('Medium') }}</flux:text>
                    <flux:heading size="xl" data-test="medium-count">{{ $summary['medium'] }}</flux:heading>
                </div>
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/50 dark:bg-sky-950/30">
                    <flux:text class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-300">{{ __('Low') }}</flux:text>
                    <flux:heading size="xl" data-test="low-count">{{ $summary['low'] }}</flux:heading>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Ingestion Freshness') }}</flux:heading>
                    <flux:text class="mt-1">
                        @if ($summary['latest_ingested_at'])
                            {{ __('Latest ingestion: :time', ['time' => \Illuminate\Support\Carbon::parse($summary['latest_ingested_at'])->toDayDateTimeString()]) }}
                        @else
                            {{ __('No ingestion snapshots yet.') }}
                        @endif
                    </flux:text>

                    <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Project') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Last Ingested') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                                @foreach ($projectFreshness as $project)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $project['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $project['last_ingested_at']?->toDayDateTimeString() ?? __('Never') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($project['freshness'] === 'fresh')
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('Fresh') }}</span>
                                            @elseif ($project['freshness'] === 'stale')
                                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Stale') }}</span>
                                            @else
                                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ __('No Data') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Ecosystem Distribution') }}</flux:heading>
                    @if ($ecosystemDistribution->isEmpty())
                        <flux:text class="mt-2">{{ __('No vulnerabilities have been ingested yet.') }}</flux:text>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($ecosystemDistribution as $ecosystem)
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <flux:text>{{ $ecosystem->ecosystem }}</flux:text>
                                        <flux:text data-test="ecosystem-{{ strtolower($ecosystem->ecosystem) }}">{{ $ecosystem->vulnerability_count }}</flux:text>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <div
                                            class="h-full bg-zinc-700 dark:bg-zinc-300"
                                            style="width: {{ max(8, (int) round(($ecosystem->vulnerability_count / max(1, $summary['total'])) * 100)) }}%;"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($summary['total'] === 0)
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('No vulnerability data yet') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Your projects are set up. Send your first ingestion snapshot to populate severity and ecosystem insights.') }}
                    </flux:text>
                </div>
            @endif
        @endif
    </div>
</x-layouts::app>
