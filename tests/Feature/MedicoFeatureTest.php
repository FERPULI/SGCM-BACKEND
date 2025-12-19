<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Medico;
use App\Models\Especialidad;

uses(RefreshDatabase::class);

test('medico stats responde', function () {

    $user = User::factory()->create([
        'rol' => 'medico'
    ]);

    $especialidad = Especialidad::factory()->create();

    Medico::factory()->create([
        'usuario_id' => $user->id,
        'especialidad_id' => $especialidad->id
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/medico/stats');

    $response->assertStatus(200);
});
