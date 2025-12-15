<?php

use App\Models\User;
use App\Models\Medico;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('endpoint horario responde correctamente para usuario autenticado', function () {

    // Crear usuario
    $user = User::factory()->create();

    // Crear medico ASOCIADO AL USUARIO (CLAVE)
    Medico::factory()->create([
        'usuario_id' => $user->id, // ✅ backend usa usuario_id
    ]);

    // Autenticación correcta con sanctum
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/medico/horarios')
        ->assertStatus(200);
});

test('endpoint horario bloquea acceso sin autenticacion', function () {

    $this->getJson('/api/medico/horarios')
        ->assertStatus(401);
});
