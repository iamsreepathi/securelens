@props([
    'action',
    'method' => 'POST',
    'team' => null,
    'submitLabel' => 'Save Team',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <flux:input
        name="name"
        :label="__('Team Name')"
        type="text"
        required
        autofocus
        :value="old('name', $team?->name)"
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
    >{{ old('description', $team?->description) }}</flux:textarea>
    @error('description')
        <flux:text class="!text-red-600 dark:!text-red-400">
            {{ $message }}
        </flux:text>
    @enderror

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">
            {{ __($submitLabel) }}
        </flux:button>

        @if ($team)
            <flux:button :href="route('teams.show', $team)" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        @else
            <flux:button :href="route('teams.index')" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        @endif
    </div>
</form>
