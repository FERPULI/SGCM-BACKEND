<?php

namespace Tests\Feature\Policies;

use App\Models\Cita;
use App\Models\User;
use App\Models\Paciente;
use App\Policies\CitaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario no medico no puede actualizar cita', function () {

    // Usuario paciente (el que intenta actualizar)
    $user = User::factory()->create([
        'rol' => 'paciente',
    ]);

    $paciente = Paciente::factory()->create([
        'usuario_id' => $user->id,
    ]);

    // OTRO usuario + OTRO paciente (dueño real de la cita)
    $otroUser = User::factory()->create([
        'rol' => 'paciente',
    ]);

    $otroPaciente = Paciente::factory()->create([
        'usuario_id' => $otroUser->id,
    ]);

    // Cita pertenece al otro paciente
    $cita = Cita::factory()->create([
        'paciente_id' => $otroPaciente->id,
    ]);

    $policy = new CitaPolicy();

    expect($policy->update($user, $cita))->toBeFalse();
});
