<x-layouts::app :title="$team->name">
    <div class="mx-auto w-full max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $team->name }}</flux:heading>
                <flux:text>{{ __('Slug: :slug', ['slug' => $team->slug]) }}</flux:text>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $team)
                    <flux:button :href="route('teams.edit', $team)" variant="primary" wire:navigate>
                        {{ __('Edit Team') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        @if (session('status') === 'team-created')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Team created successfully.') }}
            </flux:text>
        @endif

        @if (session('status') === 'team-updated')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Team updated successfully.') }}
            </flux:text>
        @endif

        @if (session('status') === 'member-added')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Member added successfully.') }}
            </flux:text>
        @endif

        @if (session('status') === 'member-role-updated')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Member role updated successfully.') }}
            </flux:text>
        @endif

        @if (session('status') === 'member-removed')
            <flux:text class="!text-green-600 dark:!text-green-400">
                {{ __('Member removed successfully.') }}
            </flux:text>
        @endif

        @error('member')
            <flux:text class="!text-red-600 dark:!text-red-400">
                {{ $message }}
            </flux:text>
        @enderror

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Members') }}</flux:text>
                    <flux:heading size="lg">{{ $team->users_count }}</flux:heading>
                </div>
                <div>
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Projects') }}</flux:text>
                    <flux:heading size="lg">{{ $team->projects_count }}</flux:heading>
                </div>
            </div>

            <div class="mt-6">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Description') }}</flux:text>
                <flux:text class="mt-2">{{ $team->description ?: __('No description provided.') }}</flux:text>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Members') }}</flux:heading>
                <flux:text>{{ __('Manage access roles for this team.') }}</flux:text>
            </div>

            @can('manageMembers', $team)
                <form method="POST" action="{{ route('teams.members.store', $team) }}" class="mb-6 grid gap-3 rounded-lg border border-zinc-200 p-4 md:grid-cols-[1fr_auto_auto] dark:border-zinc-700">
                    @csrf
                    <flux:input
                        name="email"
                        :label="__('Member Email')"
                        type="email"
                        required
                        :value="old('email')"
                        placeholder="user@example.com"
                    />

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                            {{ __('Role') }}
                        </label>
                        <select
                            name="role"
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                            required
                        >
                            @foreach ($assignableRoles as $role)
                                <option value="{{ $role }}" @selected(old('role', 'member') === $role)>
                                    {{ str($role)->replace('_', ' ')->title() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="self-end">
                        <flux:button type="submit" variant="primary">
                            {{ __('Add Member') }}
                        </flux:button>
                    </div>
                </form>

                @error('email')
                    <flux:text class="mb-4 !text-red-600 dark:!text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                @error('role')
                    <flux:text class="mb-4 !text-red-600 dark:!text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror
            @endcan

            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Email') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Role') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                        @foreach ($team->users as $member)
                            <tr>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $member->name }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $member->email }}</td>
                                <td class="px-4 py-3">
                                    @can('manageMembers', $team)
                                        <form method="POST" action="{{ route('teams.members.update', [$team, $member]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select
                                                name="role"
                                                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                @foreach ($assignableRoles as $role)
                                                    <option value="{{ $role }}" @selected($member->pivot->role === $role)>
                                                        {{ str($role)->replace('_', ' ')->title() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <flux:button type="submit" variant="ghost" size="sm">
                                                {{ __('Save') }}
                                            </flux:button>
                                        </form>
                                    @else
                                        <flux:text>{{ str($member->pivot->role)->replace('_', ' ')->title() }}</flux:text>
                                    @endcan
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('manageMembers', $team)
                                        <form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="sm">
                                                {{ __('Remove') }}
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

        <div class="flex items-center justify-between">
            <flux:button :href="route('teams.index')" variant="ghost" wire:navigate>
                {{ __('Back to Teams') }}
            </flux:button>

            @can('delete', $team)
                <form method="POST" action="{{ route('teams.destroy', $team) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger">
                        {{ __('Delete Team') }}
                    </flux:button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts::app>
