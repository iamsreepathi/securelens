<x-layouts::app :title="__('Teams')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Teams') }}</flux:heading>
                <flux:text>{{ __('Manage your teams, memberships, and project ownership boundaries.') }}</flux:text>
            </div>

            @can('create', \App\Models\Team::class)
                <flux:button :href="route('teams.create')" variant="primary" wire:navigate>
                    {{ __('New Team') }}
                </flux:button>
            @endcan
        </div>

        @if (session('status') === 'team-deleted')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Team deleted successfully.') }}
            </flux:text>
        @endif

        @if ($teams->isEmpty())
            <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:text>{{ __('You do not have any teams yet.') }}</flux:text>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Team') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Slug') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Members') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Projects') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                        @foreach ($teams as $team)
                            <tr>
                                <td class="px-4 py-3">
                                    <flux:text class="font-medium">{{ $team->name }}</flux:text>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:text>{{ $team->slug }}</flux:text>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:text>{{ $team->users_count }}</flux:text>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:text>{{ $team->projects_count }}</flux:text>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button :href="route('teams.show', $team)" variant="ghost" size="sm" wire:navigate>
                                        {{ __('View') }}
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
