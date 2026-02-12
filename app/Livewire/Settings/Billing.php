<?php

namespace App\Livewire\Settings;

use App\Support\CashierCheckoutUrlFactory;
use Laravel\Cashier\Subscription;
use Livewire\Component;

class Billing extends Component
{
    public function initiateCheckout(string $planCode): void
    {
        $user = auth()->user();
        abort_if($user === null, 403);

        $ownsAnyTeam = $user->teams()->wherePivot('role', 'owner')->exists();
        abort_unless($ownsAnyTeam, 403);

        $plan = $this->planByCode($planCode);
        abort_if($plan === null, 404);
        abort_if(($plan['is_active'] ?? false) !== true, 404);
        abort_if(! isset($plan['stripe_price_id']) || $plan['stripe_price_id'] === '', 422);

        $checkoutUrl = app(CashierCheckoutUrlFactory::class)->createForSubscription(
            $user,
            (string) $plan['stripe_price_id'],
            route('billing.edit', ['checkout' => 'success'], absolute: true),
            route('billing.edit', ['checkout' => 'cancel'], absolute: true),
        );

        $this->redirect($checkoutUrl, navigate: false);
    }

    /**
     * @return array<int, array{code: string, name: string, description: string, price_monthly_cents: int, stripe_price_id: string, is_active: bool}>
     */
    public function getPlansProperty(): array
    {
        /** @var array<int, array{code: string, name: string, description: string, price_monthly_cents: int, stripe_price_id: string, is_active: bool}> $plans */
        $plans = config('billing.plans', []);

        return array_values(array_filter($plans, fn (array $plan): bool => ($plan['is_active'] ?? false) === true));
    }

    public function getCurrentSubscriptionProperty(): ?object
    {
        $user = auth()->user();
        if ($user === null) {
            return null;
        }

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');

        if ($subscription === null) {
            return null;
        }

        $planName = collect($this->plans)
            ->firstWhere('stripe_price_id', $subscription->stripe_price)['name'] ?? $subscription->stripe_price;

        return (object) [
            'status' => $subscription->stripe_status,
            'name' => $planName,
        ];
    }

    /**
     * @return array{code: string, name: string, description: string, price_monthly_cents: int, stripe_price_id: string, is_active: bool}|null
     */
    private function planByCode(string $planCode): ?array
    {
        return collect($this->plans)->firstWhere('code', $planCode);
    }
}
