<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Especialidad;

uses(RefreshDatabase::class);

test('store especialidad request valida correctamente', function () {

    Sanctum::actingAs(User::factory()->create(['rol' => 'admin']));

    $response = $this->postJson('/api/especialidades', [
        'nombre' => 'Cardiología'
    ]);

    $response->assertStatus(201);
});

test('update especialidad request sin nombre responde 200', function () {

    Sanctum::actingAs(User::factory()->create(['rol' => 'admin']));

    $especialidad = Especialidad::create([
        'nombre' => 'Test'
    ]);

    $response = $this->putJson('/api/especialidades/' . $especialidad->id, []);

    $response->assertStatus(200);
});

test('store user request valida correctamente', function () {

    Sanctum::actingAs(User::factory()->create(['rol' => 'admin']));

    $response = $this->postJson('/api/users', [
        'nombre' => 'Juan',
        'apellidos' => 'Perez',
        'email' => 'juan@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'rol' => 'admin'
    ]);

    $response->assertStatus(201);
});

test('store cita request valida fecha y hora', function () {

    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/citas', []);

    $response->assertStatus(422);
});
