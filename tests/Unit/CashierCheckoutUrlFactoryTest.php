<?php

use App\Models\User;
use App\Support\CashierCheckoutUrlFactory;
use Laravel\Cashier\SubscriptionBuilder;

test('checkout factory applies billing address and customer email options', function () {
    $subscriptionBuilder = \Mockery::mock(SubscriptionBuilder::class);

    $user = new class extends User
    {
        public SubscriptionBuilder $subscriptionBuilder;

        public function newSubscription($type, $prices = []): SubscriptionBuilder
        {
            expect($type)->toBe('default');
            expect($prices)->toBe('price_team');

            return $this->subscriptionBuilder;
        }
    };
    $user->email = 'owner@example.com';
    $user->subscriptionBuilder = $subscriptionBuilder;

    $subscriptionBuilder->shouldReceive('checkout')
        ->once()
        ->with(
            [
                'success_url' => 'https://securelens.test/settings/billing?checkout=success',
                'cancel_url' => 'https://securelens.test/settings/billing?checkout=cancel',
                'billing_address_collection' => 'required',
                'customer_update' => [
                    'address' => 'auto',
                    'name' => 'auto',
                ],
            ],
            [
                'email' => 'owner@example.com',
            ],
        )
        ->andReturn((object) ['url' => 'https://checkout.stripe.test/session_123']);

    $checkoutUrl = app(CashierCheckoutUrlFactory::class)->createForSubscription(
        $user,
        'price_team',
        'https://securelens.test/settings/billing?checkout=success',
        'https://securelens.test/settings/billing?checkout=cancel',
    );

    expect($checkoutUrl)->toBe('https://checkout.stripe.test/session_123');
});
