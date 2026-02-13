@props([
    'action',
    'method' => 'POST',
    'project' => null,
    'submitLabel' => 'Save Project',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <flux:input
        name="name"
        :label="__('Project Name')"
        type="text"
        required
        autofocus
        :value="old('name', $project?->name)"
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
    >{{ old('description', $project?->description) }}</flux:textarea>
    @error('description')
        <flux:text class="!text-red-600 dark:!text-red-400">
            {{ $message }}
        </flux:text>
    @enderror

    <div>
        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
            {{ __('Project Status') }}
        </label>
        <select
            name="is_active"
            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
            required
        >
            <option value="1" @selected((string) old('is_active', (int) ($project?->is_active ?? true)) === '1')>{{ __('Active') }}</option>
            <option value="0" @selected((string) old('is_active', (int) ($project?->is_active ?? true)) === '0')>{{ __('Inactive') }}</option>
        </select>
    </div>
    @error('is_active')
        <flux:text class="!text-red-600 dark:!text-red-400">
            {{ $message }}
        </flux:text>
    @enderror

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">
            {{ __($submitLabel) }}
        </flux:button>

        <flux:button :href="$project ? route('projects.show', $project) : route('projects.index')" variant="ghost" wire:navigate>
            {{ __('Cancel') }}
        </flux:button>
    </div>
</form>
