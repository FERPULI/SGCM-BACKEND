<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('medico recibe 404 al crear horario si la ruta no existe', function () {
    $user = User::factory()->create([
        'rol' => 'medico',
    ]);

    $this->actingAs($user)
        ->postJson('/api/horarios', [
            'dia' => 'lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
        ])
        ->assertStatus(404);
});
