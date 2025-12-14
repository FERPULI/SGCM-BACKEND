<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use App\Models\Paciente;
use App\Models\Cita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('update cita request falla si fecha_hora_inicio es una fecha pasada', function () {

    // --------------------------------------------------
    // 1. Usuario autenticado
    // --------------------------------------------------
    $user = User::factory()->create();

    // --------------------------------------------------
    // 2. Paciente asociado al usuario
    // --------------------------------------------------
    $paciente = Paciente::factory()->create([
        'usuario_id' => $user->id,
    ]);

    // --------------------------------------------------
    // 3. Cita válida existente
    // --------------------------------------------------
    $cita = Cita::factory()->create([
        'paciente_id'       => $paciente->id,
        'fecha_hora_inicio' => now()->addDay(),
        'fecha_hora_fin'    => now()->addDay()->addMinutes(30),
    ]);

    // --------------------------------------------------
    // 4. Autenticación
    // --------------------------------------------------
    $this->actingAs($user);

    // --------------------------------------------------
    // 5. Fecha válida pero INCORRECTA (pasada)
    // --------------------------------------------------
    $fecha_pasada = Carbon::now()->subDay()->format('Y-m-d H:i:s');

    $response = $this->putJson("/api/citas/{$cita->id}", [
        'fecha_hora_inicio' => $fecha_pasada,
    ]);

    // --------------------------------------------------
    // 6. Validación esperada
    // --------------------------------------------------
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['fecha_hora_inicio']);
});
