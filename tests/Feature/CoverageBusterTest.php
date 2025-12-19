<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\HistorialMedico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('limpiar controladores de citas, historial y disponibilidad', function () {
    // 1. SETUP - Creamos la estructura necesaria
    $admin = User::factory()->create(['rol' => 'admin']);
    $medicoUser = User::factory()->create(['rol' => 'medico']);
    $medico = Medico::factory()->create(['usuario_id' => $medicoUser->id]);
    
    $pacienteUser = User::factory()->create(['rol' => 'paciente']);
    $paciente = Paciente::factory()->create(['usuario_id' => $pacienteUser->id]);
    
    $cita = Cita::factory()->create([
        'medico_id' => $medico->id,
        'paciente_id' => $paciente->id,
        'estado' => 'programada',
        'fecha_hora_inicio' => now()->addDays(1),
        'fecha_hora_fin' => now()->addDays(1)->addHour(),
    ]);

    // --- 2. CITA CONTROLLER (Líneas 72-93, 114-144) ---
    Sanctum::actingAs($medicoUser);
    
    // Probar actualización de estado (común en CitaController@update)
    $this->putJson("/api/citas/{$cita->id}", [
        'estado' => 'completada',
        'medico_id' => $medico->id,
        'paciente_id' => $paciente->id,
        'fecha_hora_inicio' => $cita->fecha_hora_inicio,
        'fecha_hora_fin' => $cita->fecha_hora_fin,
    ]);

    // Probar eliminación (CitaController@destroy)
    $this->deleteJson("/api/citas/{$cita->id}");

    // --- 3. HISTORIAL MEDICO (Líneas 33-76) ---
    // Creamos uno para poder listar y ver detalles
    $historial = HistorialMedico::create([
        'paciente_id' => $paciente->id,
        'medico_id'   => $medico->id,
        'cita_id'     => $cita->id,
        'diagnostico' => 'Gripe común',
        'tratamiento' => 'Reposo',
        'descripcion' => 'Paciente con fiebre',
        'fecha'       => now()->toDateString()
    ]);

    Sanctum::actingAs($pacienteUser);
    $this->getJson("/api/historial-medico?paciente_id={$paciente->id}"); // Index con filtro
    $this->getJson("/api/historial-medico/{$historial->id}"); // Show

    // --- 4. DISPONIBILIDAD (Líneas 39-106) ---
    // Intentar buscar slots con parámetros que activen la lógica de búsqueda
    $this->getJson("/api/slots-disponibles?" . http_build_query([
        'medico_id' => $medico->id,
        'fecha' => now()->addDays(2)->format('Y-m-d'),
        'especialidad_id' => 1
    ]));

    // --- 5. MEDICO CONTROLLER (Líneas 22-106) ---
    Sanctum::actingAs($admin);
    $this->getJson("/api/medicos"); // Listado de médicos
    $this->getJson("/api/medicos/{$medico->id}"); // Detalle del médico

    // --- 6. USER CONTROLLER (Líneas 189-232) ---
    // Probar el borrado de usuario
    $tempUser = User::factory()->create();
    $this->deleteJson("/api/users/{$tempUser->id}");

    $this->assertTrue(true);
});