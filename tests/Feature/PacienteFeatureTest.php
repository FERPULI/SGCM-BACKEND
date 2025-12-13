<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Paciente;

uses(RefreshDatabase::class);

test('paciente dashboard responde', function () {

    $user = User::factory()->create([
        'rol' => 'paciente'
    ]);

    Paciente::factory()->create([
        'usuario_id' => $user->id
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/paciente/dashboard')
        ->assertStatus(200);
});
