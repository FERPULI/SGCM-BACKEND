<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('slots disponibles devuelve 404 porque la ruta no existe', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/disponibilidad/slots')
        ->assertStatus(404);
});
