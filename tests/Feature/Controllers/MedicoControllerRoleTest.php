<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('endpoint medico stats bloquea usuario no medico', function () {

    $user = User::factory()->create(['rol' => 'paciente']);

    $this->actingAs($user)
        ->getJson('/api/medico/stats')
        ->assertStatus(403);
});
