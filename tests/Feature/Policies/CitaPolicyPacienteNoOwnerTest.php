<?php

use App\Models\User;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Cita;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('paciente no propietario no puede actualizar cita', function () {

    // Paciente dueño
    $userPacienteOwner = User::factory()->create(['rol' => 'paciente']);
    $pacienteOwner = Paciente::factory()->create([
        'usuario_id' => $userPacienteOwner->id
    ]);

    // Paciente NO dueño
    $userPacienteNoOwner = User::factory()->create(['rol' => 'paciente']);
    Paciente::factory()->create([
        'usuario_id' => $userPacienteNoOwner->id
    ]);

    $userMedico = User::factory()->create(['rol' => 'medico']);
    $medico = Medico::factory()->create([
        'usuario_id' => $userMedico->id
    ]);

    $cita = Cita::factory()->create([
        'paciente_id' => $pacienteOwner->id,
        'medico_id'   => $medico->id,
    ]);

    $this->actingAs($userPacienteNoOwner)
        ->putJson("/api/citas/{$cita->id}", [])
        ->assertForbidden();
});
