<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Medico;
use App\Models\Especialidad;

uses(RefreshDatabase::class);

test('slots disponibles responden correctamente', function () {

    Sanctum::actingAs(User::factory()->create());

    $especialidad = Especialidad::factory()->create();

    $medicoUser = User::factory()->create([
        'rol' => 'medico'
    ]);

    $medico = Medico::factory()->create([
        'usuario_id' => $medicoUser->id,
        'especialidad_id' => $especialidad->id
    ]);

    $this->getJson('/api/slots-disponibles?medico_id='.$medico->id.'&fecha='.now()->toDateString())
        ->assertStatus(200);
});
