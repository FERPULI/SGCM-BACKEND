<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Paciente;

uses(RefreshDatabase::class);

test('endpoints citas responden correctamente', function () {

    $user = User::factory()->create([
        'rol' => 'paciente'
    ]);

    Paciente::factory()->create([
        'usuario_id' => $user->id
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/citas');

    $response->assertStatus(200);
});
