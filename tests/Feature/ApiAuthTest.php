<?php

use App\Models\User;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed([SchoolSeeder::class, UserSeeder::class]);
});

function loginAs(string $email, string $password = 'password')
{
    return test()->postJson('/api/login', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'pest',
    ]);
}

test('login with the right password gives a token', function () {
    loginAs('lfernandez@school.edu.ph')
        ->assertCreated()
        ->assertJsonStructure(['token', 'name'])
        ->assertJsonPath('name', 'Liza Fernandez');
});

test('login with a wrong password gives 401', function () {
    loginAs('lfernandez@school.edu.ph', 'guess')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Wrong email or password.');
});

test('login with an unknown email gives the same 401', function () {
    loginAs('nobody@school.edu.ph')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Wrong email or password.');
});

test('login without an email gives 422', function () {
    $this->postJson('/api/login', ['password' => 'password', 'device_name' => 'pest'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('login is limited to 5 tries a minute', function () {
    foreach (range(1, 5) as $i) {
        loginAs('lfernandez@school.edu.ph', 'guess')->assertUnauthorized();
    }

    loginAs('lfernandez@school.edu.ph', 'guess')->assertStatus(429);
});

test('me needs a token', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});

test('me shows the name, email and role of the token owner', function () {
    $token = loginAs('registrar@school.edu.ph')->json('token');

    $this->withToken($token)->getJson('/api/me')
        ->assertOk()
        ->assertExactJson([
            'name' => 'Registrar',
            'email' => 'registrar@school.edu.ph',
            'role' => 'admin',
        ]);
});

test('logout cancels the token', function () {
    $user = User::where('email', 'lfernandez@school.edu.ph')->first();
    $token = $user->createToken('pest')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});
