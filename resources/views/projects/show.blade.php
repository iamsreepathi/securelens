<x-layouts::app :title="$project->name">
    <div class="mx-auto w-full max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $project->name }}</flux:heading>
                <flux:text>{{ __('Slug: :slug', ['slug' => $project->slug]) }}</flux:text>
            </div>

            @can('update', $project)
                <flux:button :href="route('projects.edit', $project)" variant="primary" wire:navigate>
                    {{ __('Project Settings') }}
                </flux:button>
            @endcan
        </div>

        @if (session('status') === 'project-created')
            <flux:text class="!text-green-600 dark:!text-green-400">{{ __('Project created successfully.') }}</flux:text>
        @endif

        @if (session('status') === 'project-updated')
            <flux:text class="!text-green-600 dark:!text-green-400">{{ __('Project updated successfully.') }}</flux:text>
        @endif

        @if (session('status') === 'project-archived')
            <flux:text class="!text-green-600 dark:!text-green-400">{{ __('Project archived successfully.') }}</flux:text>
        @endif

        @if (session('status') === 'project-team-assigned')
            <flux:text class="!text-green-600 dark:!text-green-400">{{ __('Team assigned successfully.') }}</flux:text>
        @endif

        @if (session('status') === 'project-team-unassigned')
            <flux:text class="!text-green-600 dark:!text-green-400">{{ __('Team unassigned successfully.') }}</flux:text>
        @endif

        @error('team')
            <flux:text class="!text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
        @enderror

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Status') }}</flux:text>
                    <flux:heading size="lg">{{ $project->is_active ? __('Active') : __('Inactive') }}</flux:heading>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Assigned Teams') }}</flux:text>
                    <flux:heading size="lg">{{ $project->teams->count() }}</flux:heading>
                </div>
            </div>

            <div class="mt-6">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Description') }}</flux:text>
                <flux:text class="mt-2">{{ $project->description ?: __('No description provided.') }}</flux:text>
            </div>

            <div class="mt-6">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Team Assignments') }}</flux:text>

                @can('update', $project)
                    <form method="POST" action="{{ route('projects.teams.store', $project) }}" class="mt-3 flex items-end gap-2">
                        @csrf
                        <div class="w-full">
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                {{ __('Assign Team') }}
                            </label>
                            <select
                                name="team_id"
                                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                                <option value="" disabled @selected(old('team_id') === null)>{{ __('Select a team') }}</option>
                                @foreach ($assignableTeams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id') === $team->id)>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <flux:button type="submit" variant="primary">{{ __('Assign') }}</flux:button>
                    </form>

                    @error('team_id')
                        <flux:text class="mt-2 !text-red-600 dark:!text-red-400">{{ $message }}</flux:text>
                    @enderror
                @endcan

                <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Team') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Assigned At') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                            @foreach ($project->teams as $team)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $team->name }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $team->pivot->assigned_at ? \Illuminate\Support\Carbon::parse($team->pivot->assigned_at)->toDateTimeString() : __('Unknown') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @can('update', $project)
                                            <form method="POST" action="{{ route('projects.teams.destroy', [$project, $team]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="danger" size="sm">
                                                    {{ __('Unassign') }}
                                                </flux:button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <flux:button :href="route('projects.index')" variant="ghost" wire:navigate>
                {{ __('Back to Projects') }}
            </flux:button>

            @can('update', $project)
                @if ($project->is_active)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger">
                            {{ __('Archive Project') }}
                        </flux:button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</x-layouts::app>
