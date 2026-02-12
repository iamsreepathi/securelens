<?php

use App\Models\User;
use Illuminate\Support\Str;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect(Str::isUuid($user->id))->toBeTrue();
});

test('users can not register with a duplicate email', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});
