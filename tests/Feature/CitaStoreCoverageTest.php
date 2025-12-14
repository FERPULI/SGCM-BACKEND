<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\DisponibilidadMedico;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('crear cita responde 201', function () {

    $userPaciente = User::factory()->create();
    $userMedico   = User::factory()->create();

    $paciente = Paciente::factory()->create([
        'usuario_id' => $userPaciente->id,
    ]);

    $medico = Medico::factory()->create([
        'usuario_id' => $userMedico->id,
    ]);

    $fecha = now()->addDay();

    // 🔥 CLAVE: usar dayOfWeek (0–6)
    DisponibilidadMedico::factory()->create([
        'medico_id'   => $medico->id,
        'dia_semana'  => $fecha->dayOfWeek,
        'hora_inicio' => '09:00',
        'hora_fin'    => '17:00',
    ]);

    $this->actingAs($userPaciente);

    $this->postJson('/api/citas', [
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'fecha_hora_inicio' => $fecha->copy()->setHour(10)->toDateTimeString(),
        'fecha_hora_fin' => $fecha->copy()->setHour(11)->toDateTimeString(),
    ])->assertStatus(201);
});
