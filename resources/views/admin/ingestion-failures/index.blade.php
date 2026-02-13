<x-layouts::app :title="__('Ingestion Failures')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Ingestion Failure Diagnostics') }}</flux:heading>
            <flux:text>{{ __('Review repeated ingestion failures with project/source metadata and actionable exception details.') }}</flux:text>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.ingestion-failures.index') }}" class="grid gap-3 rounded-xl border border-zinc-200 p-4 md:grid-cols-3 dark:border-zinc-700">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Project ID') }}</label>
                <input
                    type="text"
                    name="project_id"
                    value="{{ $filters['project_id'] }}"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Source') }}</label>
                <input
                    type="text"
                    name="source"
                    value="{{ $filters['source'] }}"
                    placeholder="osv"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                />
            </div>
            <div class="flex items-end gap-2">
                <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
                <flux:button :href="route('admin.ingestion-failures.index')" variant="ghost" wire:navigate>{{ __('Reset') }}</flux:button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Failed At') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Project') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Source') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Attempt') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Job') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Exception') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($failures as $failure)
                        <tr>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $failure->failed_at?->toDayDateTimeString() }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $failure->project?->name ?? 'Unknown' }}
                                <div class="text-xs text-zinc-500">{{ $failure->project_id }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $failure->source ?? 'n/a' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $failure->attempt ?? 'n/a' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $failure->job_name }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ str($failure->exception)->limit(120) }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                <details class="space-y-3">
                                    <summary class="cursor-pointer text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                        {{ __('Operational Actions') }}
                                    </summary>

                                    <form method="POST" action="{{ route('admin.ingestion-failures.retry', $failure) }}" class="space-y-2 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                                        @csrf
                                        <label class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Retry Reason') }}</label>
                                        <input
                                            type="text"
                                            name="reason"
                                            placeholder="{{ __('Describe why this retry is safe') }}"
                                            required
                                            minlength="10"
                                            maxlength="500"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                        />
                                        <label class="block text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Type retry to confirm') }}</label>
                                        <input
                                            type="text"
                                            name="confirmation"
                                            placeholder="retry"
                                            required
                                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                        />
                                        <flux:button type="submit" variant="primary">{{ __('Retry / Requeue') }}</flux:button>
                                    </form>

                                    @if ($failure->project !== null)
                                        <form method="POST" action="{{ route('admin.projects.webhook-tokens.disable', $failure->project) }}" class="space-y-2 rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                                            @csrf
                                            <label class="block text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('Disable Token Reason') }}</label>
                                            <input
                                                type="text"
                                                name="reason"
                                                placeholder="{{ __('Explain compromise or containment reason') }}"
                                                required
                                                minlength="10"
                                                maxlength="500"
                                                class="w-full rounded-md border border-amber-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-amber-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            />
                                            <label class="block text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('Type disable to confirm') }}</label>
                                            <input
                                                type="text"
                                                name="confirmation"
                                                placeholder="disable"
                                                required
                                                class="w-full rounded-md border border-amber-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-amber-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            />
                                            <flux:button type="submit" variant="primary">{{ __('Disable Active Tokens') }}</flux:button>
                                        </form>
                                    @endif
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('No ingestion failures matched the selected filters.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $failures->links() }}
        </div>
    </div>
</x-layouts::app>
