<?php

return [
    'plans' => [
        [
            'code' => 'starter',
            'name' => 'Starter',
            'description' => 'Basic coverage for small teams.',
            'price_monthly_cents' => 0,
            'stripe_price_id' => (string) env('STRIPE_PRICE_STARTER', ''),
            'is_active' => true,
        ],
        [
            'code' => 'team',
            'name' => 'Team',
            'description' => 'Expanded scans and collaboration controls.',
            'price_monthly_cents' => 4900,
            'stripe_price_id' => (string) env('STRIPE_PRICE_TEAM', ''),
            'is_active' => true,
        ],
        [
            'code' => 'enterprise',
            'name' => 'Enterprise',
            'description' => 'Advanced controls and premium support.',
            'price_monthly_cents' => 14900,
            'stripe_price_id' => (string) env('STRIPE_PRICE_ENTERPRISE', ''),
            'is_active' => true,
        ],
    ],
];
