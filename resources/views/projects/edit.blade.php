<x-layouts::app :title="__('Project Settings')">
    <div class="mx-auto w-full max-w-2xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Project Settings') }}</flux:heading>
            <flux:text>{{ __('Update project metadata and active/inactive behavior controls.') }}</flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <x-projects.form
                :action="route('projects.update', $project)"
                method="PUT"
                :project="$project"
                submit-label="Save Project"
            />
        </div>
    </div>
</x-layouts::app>
