<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-5 sm:space-y-6">
        <div>
            <flux:heading class="heading-display dashboard-title" size="xl">{{ __('Security Overview') }}</flux:heading>
            <flux:text class="dashboard-copy max-w-3xl text-sm sm:text-base">{{ __('Project-level vulnerability posture, ingestion freshness, and ecosystem spread.') }}</flux:text>
        </div>

        @if (! $hasProjects)
            <div class="dashboard-panel">
                <flux:heading class="dashboard-title" size="lg">{{ __('Connect your first project') }}</flux:heading>
                <flux:text class="dashboard-copy mt-2">
                    {{ __('Create a project and configure your vulnerability ingestion integration to start seeing risk summaries.') }}
                </flux:text>
                <div class="mt-4">
                    <flux:button :href="route('projects.create')" variant="primary" wire:navigate>
                        {{ __('Create Project') }}
                    </flux:button>
                </div>
            </div>
        @else
            <div>
                <flux:text class="dashboard-muted text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Severity Snapshot') }}</flux:text>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
                <div class="rounded-2xl border border-rose-300/80 bg-gradient-to-br from-rose-100 to-white p-4 shadow-lg shadow-zinc-300/35 dark:border-rose-500/40 dark:from-rose-500/25 dark:to-zinc-900/70 dark:shadow-zinc-950/50 sm:p-5">
                    <flux:text class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-200">{{ __('Critical') }}</flux:text>
                    <flux:heading class="text-zinc-900 dark:text-white" size="xl" data-test="critical-count">{{ $summary['critical'] }}</flux:heading>
                    <flux:text class="dashboard-muted mt-1 text-xs">{{ __('Immediate action required') }}</flux:text>
                </div>
                <div class="rounded-2xl border border-amber-300/80 bg-gradient-to-br from-amber-100 to-white p-4 shadow-lg shadow-zinc-300/35 dark:border-amber-500/40 dark:from-amber-500/25 dark:to-zinc-900/70 dark:shadow-zinc-950/50 sm:p-5">
                    <flux:text class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-200">{{ __('High') }}</flux:text>
                    <flux:heading class="text-zinc-900 dark:text-white" size="xl" data-test="high-count">{{ $summary['high'] }}</flux:heading>
                    <flux:text class="dashboard-muted mt-1 text-xs">{{ __('Prioritize within sprint') }}</flux:text>
                </div>
                <div class="rounded-2xl border border-yellow-300/80 bg-gradient-to-br from-yellow-100 to-white p-4 shadow-lg shadow-zinc-300/35 dark:border-yellow-500/40 dark:from-yellow-500/25 dark:to-zinc-900/70 dark:shadow-zinc-950/50 sm:p-5">
                    <flux:text class="text-xs uppercase tracking-wide text-yellow-700 dark:text-yellow-200">{{ __('Medium') }}</flux:text>
                    <flux:heading class="text-zinc-900 dark:text-white" size="xl" data-test="medium-count">{{ $summary['medium'] }}</flux:heading>
                    <flux:text class="dashboard-muted mt-1 text-xs">{{ __('Track and schedule fixes') }}</flux:text>
                </div>
                <div class="rounded-2xl border border-sky-300/80 bg-gradient-to-br from-sky-100 to-white p-4 shadow-lg shadow-zinc-300/35 dark:border-sky-500/40 dark:from-sky-500/25 dark:to-zinc-900/70 dark:shadow-zinc-950/50 sm:p-5">
                    <flux:text class="text-xs uppercase tracking-wide text-sky-700 dark:text-sky-200">{{ __('Low') }}</flux:text>
                    <flux:heading class="text-zinc-900 dark:text-white" size="xl" data-test="low-count">{{ $summary['low'] }}</flux:heading>
                    <flux:text class="dashboard-muted mt-1 text-xs">{{ __('Monitor baseline risk') }}</flux:text>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="dashboard-panel">
                    <flux:heading class="dashboard-title" size="lg">{{ __('Ingestion Freshness') }}</flux:heading>
                    <flux:text class="dashboard-copy mt-1">
                        @if ($summary['latest_ingested_at'])
                            {{ __('Latest ingestion: :time', ['time' => \Illuminate\Support\Carbon::parse($summary['latest_ingested_at'])->toDayDateTimeString()]) }}
                        @else
                            {{ __('No ingestion snapshots yet.') }}
                        @endif
                    </flux:text>

                    <div class="dashboard-table-wrap">
                        <table class="min-w-[36rem] divide-y sm:min-w-full">
                            <caption class="sr-only">{{ __('Project ingestion freshness table') }}</caption>
                            <thead class="dashboard-table-head-row">
                                <tr>
                                    <th scope="col" class="dashboard-table-head">{{ __('Project') }}</th>
                                    <th scope="col" class="dashboard-table-head">{{ __('Last Ingested') }}</th>
                                    <th scope="col" class="dashboard-table-head">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="dashboard-table-body">
                                @foreach ($projectFreshness as $project)
                                    <tr class="transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-900/55">
                                        <td class="dashboard-table-cell-strong">{{ $project['name'] }}</td>
                                        <td class="dashboard-table-cell">
                                            {{ $project['last_ingested_at']?->toDayDateTimeString() ?? __('Never') }}
                                        </td>
                                        <td class="px-4 py-3 text-xs sm:text-sm">
                                            @if ($project['freshness'] === 'fresh')
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('Fresh') }}</span>
                                            @elseif ($project['freshness'] === 'stale')
                                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Stale') }}</span>
                                            @else
                                                <span class="rounded-full bg-zinc-200 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ __('No Data') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dashboard-panel">
                    <flux:heading class="dashboard-title" size="lg">{{ __('Ecosystem Distribution') }}</flux:heading>
                    @if ($ecosystemDistribution->isEmpty())
                        <flux:text class="dashboard-copy mt-2">{{ __('No vulnerabilities have been ingested yet.') }}</flux:text>
                    @else
                        <div class="mt-2 flex items-center justify-between">
                            <flux:text class="dashboard-muted text-xs uppercase tracking-[0.16em]">{{ __('Ecosystem Share') }}</flux:text>
                            <flux:text class="dashboard-copy text-xs">{{ __('Total: :count', ['count' => $summary['total']]) }}</flux:text>
                        </div>
                        <div class="mt-4 space-y-3">
                            @foreach ($ecosystemDistribution as $ecosystem)
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <flux:text class="dashboard-copy text-sm sm:text-base">{{ $ecosystem->ecosystem }}</flux:text>
                                        <flux:text class="dashboard-copy text-sm sm:text-base" data-test="ecosystem-{{ strtolower($ecosystem->ecosystem) }}">{{ $ecosystem->vulnerability_count }}</flux:text>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                        <div
                                            class="h-full bg-linear-to-r from-emerald-300 via-cyan-300 to-blue-300"
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
                <div class="dashboard-panel">
                    <flux:heading class="dashboard-title" size="lg">{{ __('No vulnerability data yet') }}</flux:heading>
                    <flux:text class="dashboard-copy mt-2">
                        {{ __('Your projects are set up. Send your first ingestion snapshot to populate severity and ecosystem insights.') }}
                    </flux:text>
                </div>
            @endif
        @endif
    </div>
</x-layouts::app>
