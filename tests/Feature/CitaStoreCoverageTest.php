<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\DisponibilidadMedico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('cobertura de validacion en update cita', function () {
    $user = User::factory()->create(['rol' => 'medico']);
    Sanctum::actingAs($user);
    
    $medico = Medico::factory()->create(['usuario_id' => $user->id]);
    $cita = Cita::factory()->create(['medico_id' => $medico->id]);

    // Enviamos una fecha válida para Carbon (YYYY-MM-DD) 
    // pero antigua para que la lógica de negocio dispare el 422
    $this->putJson("/api/citas/{$cita->id}", [
        'fecha_hora_inicio' => '2000-01-01 10:00:00', 
        'fecha_hora_fin' => '2000-01-01 11:00:00'
    ])->assertStatus(422);
});

test('crear cita falla por validacion 422', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Enviar vacío para cubrir las reglas de validación 'required'
    $this->postJson('/api/citas', [])
         ->assertStatus(422);
});