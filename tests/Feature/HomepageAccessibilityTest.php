<?php

test('homepage includes skip navigation and primary landmark hooks', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSeeText('Skip to main content');
    $response->assertSee('id="main-content"', false);
    $response->assertSee('aria-label="Primary"', false);
});

test('homepage ctas expose visible keyboard focus styles', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('focus-visible:ring-2', false);
    $response->assertSee('focus-visible:ring-offset-2', false);
});
