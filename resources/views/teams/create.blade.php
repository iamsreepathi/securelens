<x-layouts::app :title="__('Create Team')">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Create Team') }}</flux:heading>
            <flux:text>{{ __('Choose a name and optional description. A unique slug will be generated automatically.') }}</flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <x-teams.form
                :action="route('teams.store')"
                method="POST"
                submit-label="Create Team"
            />
        </div>
    </div>
</x-layouts::app>
