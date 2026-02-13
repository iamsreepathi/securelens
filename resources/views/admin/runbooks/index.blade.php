<x-layouts::app :title="__('Operations Runbooks')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Operations Runbooks') }}</flux:heading>
            <flux:text>{{ __('Versioned incident response guides for ingestion and queue recovery workflows.') }}</flux:text>
            <flux:text class="mt-1">{{ __('Published runbooks: :count', ['count' => $runbookCount]) }}</flux:text>
        </div>

        <div class="grid gap-4">
            @foreach ($runbooks as $runbook)
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ $runbook['title'] }}</flux:heading>
                            <flux:text class="mt-1">{{ $runbook['objective'] }}</flux:text>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ __('Version: :version', ['version' => $runbook['version']]) }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-3">
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <flux:heading size="sm">{{ __('Triage Steps') }}</flux:heading>
                            <ol class="mt-3 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                                @foreach ($runbook['triage_steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>

                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <flux:heading size="sm">{{ __('Escalation') }}</flux:heading>
                            <ul class="mt-3 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                                @foreach ($runbook['escalation'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <flux:heading size="sm">{{ __('Rollback Guidance') }}</flux:heading>
                            <ul class="mt-3 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                                @foreach ($runbook['rollback'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
