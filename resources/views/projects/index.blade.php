<x-layouts::app :title="__('Projects')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Projects') }}</flux:heading>
                <flux:text>{{ __('Manage project lifecycle and activation state.') }}</flux:text>
            </div>

            @can('create', \App\Models\Project::class)
                <flux:button :href="route('projects.create')" variant="primary" wire:navigate>
                    {{ __('New Project') }}
                </flux:button>
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Project') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Slug') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Teams') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse ($projects as $project)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $project->name }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $project->slug }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $project->is_active ? __('Active') : __('Inactive') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $project->teams_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button :href="route('projects.show', $project)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('No projects are assigned to your teams yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
