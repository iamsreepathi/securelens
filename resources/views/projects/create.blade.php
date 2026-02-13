<x-layouts::app :title="__('Create Project')">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Create Project') }}</flux:heading>
            <flux:text>{{ __('Create a project and assign it to one of your teams.') }}</flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
                @csrf

                <flux:input
                    name="name"
                    :label="__('Project Name')"
                    type="text"
                    required
                    autofocus
                    :value="old('name')"
                />
                @error('name')
                    <flux:text class="!text-red-600 dark:!text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                <flux:textarea
                    name="description"
                    :label="__('Description')"
                    rows="4"
                >{{ old('description') }}</flux:textarea>
                @error('description')
                    <flux:text class="!text-red-600 dark:!text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Owning Team') }}
                    </label>
                    <select
                        name="team_id"
                        class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                        required
                    >
                        <option value="" disabled @selected(old('team_id') === null)>{{ __('Select a team') }}</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') === $team->id)>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('team_id')
                    <flux:text class="!text-red-600 dark:!text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ __('Create Project') }}
                    </flux:button>

                    <flux:button :href="route('projects.index')" variant="ghost" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
