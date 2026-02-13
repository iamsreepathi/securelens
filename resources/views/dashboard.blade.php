<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div>
            <flux:heading class="heading-display text-white" size="xl">{{ __('Security Overview') }}</flux:heading>
            <flux:text class="text-zinc-300">{{ __('Project-level vulnerability posture, ingestion freshness, and ecosystem spread.') }}</flux:text>
        </div>

        @if (! $hasProjects)
            <div class="surface-panel p-6">
                <flux:heading class="text-zinc-100" size="lg">{{ __('Connect your first project') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-300">
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
                <div class="rounded-2xl border border-rose-500/40 bg-gradient-to-br from-rose-500/25 to-zinc-900/70 p-4 shadow-lg shadow-zinc-950/50">
                    <flux:text class="text-xs uppercase tracking-wide text-rose-200">{{ __('Critical') }}</flux:text>
                    <flux:heading class="text-white" size="xl" data-test="critical-count">{{ $summary['critical'] }}</flux:heading>
                </div>
                <div class="rounded-2xl border border-amber-500/40 bg-gradient-to-br from-amber-500/25 to-zinc-900/70 p-4 shadow-lg shadow-zinc-950/50">
                    <flux:text class="text-xs uppercase tracking-wide text-amber-200">{{ __('High') }}</flux:text>
                    <flux:heading class="text-white" size="xl" data-test="high-count">{{ $summary['high'] }}</flux:heading>
                </div>
                <div class="rounded-2xl border border-yellow-500/40 bg-gradient-to-br from-yellow-500/25 to-zinc-900/70 p-4 shadow-lg shadow-zinc-950/50">
                    <flux:text class="text-xs uppercase tracking-wide text-yellow-200">{{ __('Medium') }}</flux:text>
                    <flux:heading class="text-white" size="xl" data-test="medium-count">{{ $summary['medium'] }}</flux:heading>
                </div>
                <div class="rounded-2xl border border-sky-500/40 bg-gradient-to-br from-sky-500/25 to-zinc-900/70 p-4 shadow-lg shadow-zinc-950/50">
                    <flux:text class="text-xs uppercase tracking-wide text-sky-200">{{ __('Low') }}</flux:text>
                    <flux:heading class="text-white" size="xl" data-test="low-count">{{ $summary['low'] }}</flux:heading>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="surface-panel p-6">
                    <flux:heading class="text-zinc-100" size="lg">{{ __('Ingestion Freshness') }}</flux:heading>
                    <flux:text class="mt-1 text-zinc-300">
                        @if ($summary['latest_ingested_at'])
                            {{ __('Latest ingestion: :time', ['time' => \Illuminate\Support\Carbon::parse($summary['latest_ingested_at'])->toDayDateTimeString()]) }}
                        @else
                            {{ __('No ingestion snapshots yet.') }}
                        @endif
                    </flux:text>

                    <div class="mt-4 overflow-hidden rounded-xl border border-zinc-700/70">
                        <table class="min-w-full divide-y divide-zinc-700/80">
                            <thead class="bg-zinc-900/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Project') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Last Ingested') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-700/70 bg-zinc-950/45">
                                @foreach ($projectFreshness as $project)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-zinc-100">{{ $project['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-zinc-300">
                                            {{ $project['last_ingested_at']?->toDayDateTimeString() ?? __('Never') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($project['freshness'] === 'fresh')
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ __('Fresh') }}</span>
                                            @elseif ($project['freshness'] === 'stale')
                                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Stale') }}</span>
                                            @else
                                                <span class="rounded-full bg-zinc-800 px-2 py-1 text-xs font-medium text-zinc-300">{{ __('No Data') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="surface-panel p-6">
                    <flux:heading class="text-zinc-100" size="lg">{{ __('Ecosystem Distribution') }}</flux:heading>
                    @if ($ecosystemDistribution->isEmpty())
                        <flux:text class="mt-2 text-zinc-300">{{ __('No vulnerabilities have been ingested yet.') }}</flux:text>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($ecosystemDistribution as $ecosystem)
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <flux:text class="text-zinc-200">{{ $ecosystem->ecosystem }}</flux:text>
                                        <flux:text class="text-zinc-200" data-test="ecosystem-{{ strtolower($ecosystem->ecosystem) }}">{{ $ecosystem->vulnerability_count }}</flux:text>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-800">
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
                <div class="surface-panel p-6">
                    <flux:heading class="text-zinc-100" size="lg">{{ __('No vulnerability data yet') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-300">
                        {{ __('Your projects are set up. Send your first ingestion snapshot to populate severity and ecosystem insights.') }}
                    </flux:text>
                </div>
            @endif
        @endif
    </div>
</x-layouts::app>
