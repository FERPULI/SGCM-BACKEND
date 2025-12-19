<?php

use App\Models\User;
use App\Models\HistorialMedico;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('paciente recibe 404 al intentar ver historial si la ruta no existe', function () {

    $user = User::factory()->create([
        'rol' => 'paciente',
    ]);

    HistorialMedico::factory()->create([
        'paciente_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->getJson('/api/historial')
        ->assertStatus(404);
});
