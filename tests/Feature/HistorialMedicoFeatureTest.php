<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('paciente autenticado puede ver su historial medico', function () {

    $user = User::factory()->create([
        'rol' => 'paciente'
    ]);

    Paciente::factory()->create([
        'usuario_id' => $user->id
    ]);

    $this->actingAs($user);

    $this->getJson('/api/paciente/historial')
        ->assertStatus(200);
});
