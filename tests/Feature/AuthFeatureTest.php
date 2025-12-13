<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('register endpoint responde correctamente', function () {
    $response = $this->postJson('/api/auth/register', [
        'nombre' => 'Juan',
        'apellidos' => 'Pérez',
        'email' => 'juan@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol' => 'paciente', // requerido por migration real
    ]);

    $response->assertStatus(201);
});

test('login endpoint responde correctamente', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'rol' => 'paciente',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
});

test('logout endpoint requiere autenticacion', function () {
    $response = $this->postJson('/api/auth/logout');
    $response->assertStatus(401);
});
