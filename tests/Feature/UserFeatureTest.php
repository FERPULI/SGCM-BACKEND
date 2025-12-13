<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(RefreshDatabase::class);

test('index users responde', function () {

    Sanctum::actingAs(User::factory()->create(['rol' => 'admin']));

    $response = $this->getJson('/api/users');

    $response->assertStatus(200);
});

test('store user responde', function () {

    Sanctum::actingAs(User::factory()->create(['rol' => 'admin']));

    $response = $this->postJson('/api/users', [
        'nombre' => 'Nuevo',
        'apellidos' => 'Usuario',
        'email' => 'nuevo@demo.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol' => 'admin'
    ]);

    $response->assertStatus(201);
});

test('show user responde', function () {

    $user = User::factory()->create(['rol' => 'admin']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/users/' . $user->id);

    $response->assertStatus(200);
});
