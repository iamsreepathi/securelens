<?php

use App\Livewire\Settings\Billing;
use App\Models\Team;
use App\Models\User;
use App\Support\CashierCheckoutUrlFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function createCashierSubscription(User $user, string $status = 'active', ?string $endsAt = null, string $price = 'price_team'): void
{
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(14),
        'stripe_status' => $status,
        'stripe_price' => $price,
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => $endsAt,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

beforeEach(function () {
    config()->set('billing.plans', [
        [
            'code' => 'team',
            'name' => 'Team',
            'description' => 'Team plan',
            'price_monthly_cents' => 4900,
            'stripe_price_id' => 'price_team',
            'is_active' => true,
        ],
        [
            'code' => 'enterprise',
            'name' => 'Enterprise',
            'description' => 'Enterprise plan',
            'price_monthly_cents' => 14900,
            'stripe_price_id' => 'price_enterprise',
            'is_active' => true,
        ],
    ]);
});

test('team owner can initiate a cashier checkout from billing page', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner);

    app()->bind(CashierCheckoutUrlFactory::class, fn () => new class extends CashierCheckoutUrlFactory
    {
        public function createForSubscription(User $user, string $priceId, string $successUrl, string $cancelUrl): string
        {
            expect($priceId)->toBe('price_team');

            return 'https://checkout.stripe.test/session_123';
        }
    });

    Livewire::test(Billing::class)
        ->call('initiateCheckout', 'team')
        ->assertRedirect('https://checkout.stripe.test/session_123');
});

test('billing page reflects active subscription status', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($owner->id, ['role' => 'owner']);
    createCashierSubscription($owner, 'active', null, 'price_team');

    $this->actingAs($owner);

    Livewire::test(Billing::class)
        ->assertSee('Current Subscription')
        ->assertSee('Active')
        ->assertSee('Team');
});

test('non-owner cannot activate a billing plan', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($member->id, ['role' => 'member']);

    $this->actingAs($member);

    Livewire::test(Billing::class)
        ->call('initiateCheckout', 'team')
        ->assertForbidden();
});
