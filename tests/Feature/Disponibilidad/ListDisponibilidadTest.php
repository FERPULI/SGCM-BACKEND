<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('medico recibe 404 al listar disponibilidad si la ruta no existe', function () {
    $user = User::factory()->create([
        'rol' => 'medico',
    ]);

    $this->actingAs($user)
        ->getJson('/api/disponibilidad')
        ->assertStatus(404);
});
