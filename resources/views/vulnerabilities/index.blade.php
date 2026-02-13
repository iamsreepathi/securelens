<x-layouts::app :title="__('Vulnerabilities')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vulnerabilities') }}</flux:heading>
                <flux:text>{{ __('Filter by severity, ecosystem, source, fix availability, and package name.') }}</flux:text>
            </div>
            <flux:text data-test="result-count">{{ trans_choice(':count result|:count results', $vulnerabilities->total(), ['count' => $vulnerabilities->total()]) }}</flux:text>
        </div>

        <form method="GET" action="{{ route('vulnerabilities.index') }}" class="grid gap-3 rounded-xl border border-zinc-200 p-4 md:grid-cols-6 dark:border-zinc-700">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Package Search') }}</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="openssl"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Severity') }}</label>
                <select name="severity" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                    <option value="">{{ __('Any') }}</option>
                    <option value="critical" @selected($filters['severity'] === 'critical')>{{ __('Critical') }}</option>
                    <option value="high" @selected($filters['severity'] === 'high')>{{ __('High') }}</option>
                    <option value="medium" @selected($filters['severity'] === 'medium')>{{ __('Medium') }}</option>
                    <option value="low" @selected($filters['severity'] === 'low')>{{ __('Low') }}</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Ecosystem') }}</label>
                <select name="ecosystem" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($ecosystems as $ecosystem)
                        <option value="{{ $ecosystem }}" @selected($filters['ecosystem'] === $ecosystem)>{{ $ecosystem }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Source') }}</label>
                <select name="source" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source }}" @selected($filters['source'] === $source)>{{ $source }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Fix Available') }}</label>
                <select name="has_fix" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                    <option value="">{{ __('Any') }}</option>
                    <option value="yes" @selected($filters['has_fix'] === 'yes')>{{ __('Yes') }}</option>
                    <option value="no" @selected($filters['has_fix'] === 'no')>{{ __('No') }}</option>
                </select>
            </div>

            <div class="md:col-span-6 flex items-center gap-2">
                <flux:button type="submit" variant="primary">{{ __('Apply Filters') }}</flux:button>
                <flux:button :href="route('vulnerabilities.index')" variant="ghost" wire:navigate>{{ __('Reset') }}</flux:button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Package') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Project') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Severity') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('CVSS') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Ecosystem') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Source') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Fix') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($vulnerabilities as $vulnerability)
                        <tr>
                            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                <flux:link :href="route('vulnerabilities.show', $vulnerability)" wire:navigate>
                                    {{ $vulnerability->package_name }}
                                </flux:link>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $vulnerability->project?->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ str($vulnerability->severity())->title() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $vulnerability->cvss_score ?? 'n/a' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $vulnerability->ecosystem }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $vulnerability->source }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $vulnerability->fixed_version ? __('Available') : __('None') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('No vulnerabilities match the current filter set.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $vulnerabilities->links() }}
        </div>
    </div>
</x-layouts::app>
