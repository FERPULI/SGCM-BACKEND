<?php

use App\Models\User;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Cita;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('medico no propietario no puede actualizar cita', function () {

    // Paciente dueño de la cita
    $userPaciente = User::factory()->create(['rol' => 'paciente']);
    $paciente = Paciente::factory()->create([
        'usuario_id' => $userPaciente->id
    ]);

    // Médico dueño de la cita
    $userMedicoOwner = User::factory()->create(['rol' => 'medico']);
    $medicoOwner = Medico::factory()->create([
        'usuario_id' => $userMedicoOwner->id
    ]);

    // Médico NO dueño (⚠️ AQUÍ estaba el problema)
    $userMedicoNoOwner = User::factory()->create(['rol' => 'medico']);
    Medico::factory()->create([
        'usuario_id' => $userMedicoNoOwner->id
    ]);

    $cita = Cita::factory()->create([
        'paciente_id' => $paciente->id,
        'medico_id'   => $medicoOwner->id,
    ]);

    $this->actingAs($userMedicoNoOwner)
        ->putJson("/api/citas/{$cita->id}", [])
        ->assertForbidden();
});


