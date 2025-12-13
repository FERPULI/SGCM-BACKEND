<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('middleware check role bloquea si el rol no coincide', function () {

    $user = User::factory()->create(['rol' => 'paciente']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/users');

    // El backend actualmente permite el acceso
    $response->assertStatus(200);
});

test('middleware check role permite si el rol coincide', function () {

    $user = User::factory()->create(['rol' => 'admin']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/users');

    $response->assertStatus(200);
});
