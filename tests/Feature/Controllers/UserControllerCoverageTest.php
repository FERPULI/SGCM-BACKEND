<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @covers \App\Http\Controllers\API\UserController
 */
test('index users responde 200', function () {

    $admin = User::factory()->create([
        'rol' => 'admin'
    ]);

    $this->actingAs($admin);

    $this->getJson('/api/users')
        ->assertStatus(200);
});

/**
 * @covers \App\Http\Controllers\API\UserController
 */
test('store user responde 201', function () {

    $admin = User::factory()->create([
        'rol' => 'admin'
    ]);

    $this->actingAs($admin);

    $this->postJson('/api/users', [
        'nombres' => 'Juan',
        'apellidos' => 'Perez',
        'nombre' => 'Juan Perez',
        'email' => 'nuevo@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol' => 'paciente',
        'fecha_nacimiento' => '1995-05-10',
        'telefono' => '999888777'
    ])->assertStatus(201);
});
