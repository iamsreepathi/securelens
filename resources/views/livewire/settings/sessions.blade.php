<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Session Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Sessions & Security')" :subheading="__('Review active sessions and log out from other devices')">
        <div class="mt-6 space-y-6">
            <div class="space-y-4">
                @forelse ($sessions as $session)
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between gap-3">
                            <flux:text class="font-medium">
                                {{ $session['ip_address'] ?? __('Unknown IP') }}
                            </flux:text>

                            @if ($session['is_current'])
                                <flux:badge color="green">{{ __('Current Device') }}</flux:badge>
                            @endif
                        </div>

                        <flux:text class="mt-2 text-sm">
                            {{ $session['user_agent'] ?? __('Unknown device') }}
                        </flux:text>

                        <flux:text class="mt-1 text-xs">
                            {{ __('Last active: :time', ['time' => $session['last_active_at']]) }}
                        </flux:text>
                    </div>
                @empty
                    <flux:text>{{ __('No active sessions found.') }}</flux:text>
                @endforelse
            </div>

            <form method="POST" wire:submit="logoutOtherSessions" class="space-y-4">
                <flux:input
                    wire:model="current_password"
                    :label="__('Current password')"
                    type="password"
                    required
                    autocomplete="current-password"
                />

                <div class="flex items-center gap-4">
                    <flux:button variant="danger" type="submit">
                        {{ __('Log out other sessions') }}
                    </flux:button>

                    <x-action-message on="sessions-updated">
                        {{ __('Sessions updated.') }}
                    </x-action-message>
                </div>
            </form>
        </div>
    </x-settings.layout>
</section>
