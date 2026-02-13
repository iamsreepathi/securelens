<?php

use App\Models\User;

test('homepage delivery remains lightweight and avoids inline bundled styles', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('<style>', false);
    $response->assertSee('rel="preconnect"', false);

    $content = $response->getContent();

    expect($content)->not->toBeFalse();
    expect(strlen((string) $content))->toBeLessThan(22000);
});

test('homepage cta state adapts for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertSeeText('Open Dashboard');
    $response->assertDontSeeText('Sign in');
});
