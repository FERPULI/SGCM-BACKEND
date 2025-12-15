<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store cita falla si falta fecha_hora_inicio', function () {

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/citas', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fecha_hora_inicio']);
});
