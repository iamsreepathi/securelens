<x-layouts::app :title="__('Admin Audit Logs')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Admin Audit Logs') }}</flux:heading>
            <flux:text>{{ __('Trace privileged operations with actor, action, target, and before/after change context.') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-3 rounded-xl border border-zinc-200 p-4 md:grid-cols-4 dark:border-zinc-700">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Search') }}</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('actor, action, target') }}"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Action') }}</label>
                <select
                    name="action"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($actionOptions as $action)
                        <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Actor ID') }}</label>
                <input
                    type="text"
                    name="actor_id"
                    value="{{ $filters['actor_id'] }}"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Target Type') }}</label>
                <select
                    name="target_type"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($targetTypeOptions as $targetType)
                        <option value="{{ $targetType }}" @selected($filters['target_type'] === $targetType)>{{ $targetType }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 md:col-span-4">
                <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
                <flux:button :href="route('admin.audit-logs.index')" variant="ghost" wire:navigate>{{ __('Reset') }}</flux:button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Timestamp') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Actor') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Action') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Target') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Before / After') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Metadata') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->created_at?->toDayDateTimeString() }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $log->actor?->name ?? __('System') }}
                                <div class="text-xs text-zinc-500">{{ $log->actor_user_id }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $log->target_type }}
                                <div class="text-xs text-zinc-500">{{ $log->target_id }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">
                                <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Before:') }}</span> {{ json_encode($log->before_state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</div>
                                <div class="mt-1"><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('After:') }}</span> {{ json_encode($log->after_state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">{{ json_encode($log->metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('No audit logs matched the selected filters.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts::app>
