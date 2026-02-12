<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Billing Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Billing & Subscription')" :subheading="__('Manage plan activation and view subscription status')">
        @php($subscription = $this->currentSubscription)

        <div class="mt-6 space-y-6">
            @if ($subscription)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
                        <flux:text class="font-medium">{{ __('Current Subscription') }}</flux:text>
                        <flux:badge color="{{ $subscription->status === 'active' ? 'green' : 'yellow' }}">
                            {{ \Illuminate\Support\Str::title($subscription->status) }}
                        </flux:badge>
                    </div>
                    <flux:text class="mt-2 text-sm">
                        {{ $subscription->name }}
                    </flux:text>
                </div>
            @endif

            @if (! auth()->user()?->teams()->wherePivot('role', 'owner')->exists())
                <flux:callout icon="exclamation-circle" variant="warning" heading="{{ __('No owner team found') }}">
                    {{ __('You must be an owner of a team to activate a billing plan.') }}
                </flux:callout>
            @else
                <div class="space-y-4">
                    @foreach ($this->plans as $plan)
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <flux:text class="font-medium">{{ $plan['name'] }}</flux:text>
                                    <flux:text class="mt-1 text-sm">
                                        {{ $plan['description'] }}
                                    </flux:text>
                                    <flux:text class="mt-2 text-sm">
                                        {{ '$'.number_format($plan['price_monthly_cents'] / 100, 2).' / month' }}
                                    </flux:text>
                                </div>

                                <flux:button
                                    variant="primary"
                                    wire:click="initiateCheckout('{{ $plan['code'] }}')"
                                >
                                    {{ __('Activate Plan') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
