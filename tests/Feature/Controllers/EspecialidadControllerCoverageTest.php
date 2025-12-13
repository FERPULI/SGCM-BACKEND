<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('especialidad index responde correctamente', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    $this->getJson('/api/especialidades')
        ->assertStatus(200);
});
