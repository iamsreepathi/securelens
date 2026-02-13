<?php

namespace App\Support;

use App\Models\User;

class CashierCheckoutUrlFactory
{
    public function createForSubscription(User $user, string $priceId, string $successUrl, string $cancelUrl): string
    {
        $checkout = $user->newSubscription('default', $priceId)->checkout(
            [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'billing_address_collection' => 'required',
                'customer_update' => [
                    'address' => 'auto',
                    'name' => 'auto',
                ],
            ],
            [
                'email' => $user->email,
            ],
        );

        return (string) $checkout->url;
    }
}
