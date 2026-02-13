<x-layouts::app :title="__('Edit Team')">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Edit Team') }}</flux:heading>
            <flux:text>{{ __('Update team details. If the name changes, the slug is regenerated with uniqueness guarantees.') }}</flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <x-teams.form
                :action="route('teams.update', $team)"
                method="PUT"
                :team="$team"
                submit-label="Save Changes"
            />
        </div>
    </div>
</x-layouts::app>
