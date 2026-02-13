<?php

test('homepage presents the refreshed hero narrative and primary cta structure', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSeeText('Keep every project releasable with live risk clarity.');
    $response->assertSeeText('Start Triage');
    $response->assertSeeText('See How It Works');
    $response->assertSeeText('Signal Over Noise');
    $response->assertSeeText('Operator Confidence');
    $response->assertSeeText('Problem / Solution');
    $response->assertSeeText('Trusted by Builders');
    $response->assertSeeText('Upgrade your release confidence this sprint.');
    $response->assertSee('class="cta-primary"', false);
    $response->assertSee('class="heading-display', false);
});
