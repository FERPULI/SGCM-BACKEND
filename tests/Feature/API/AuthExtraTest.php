<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('cobertura extra para auth controller', function () {
    // 1. Crear usuario de prueba
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    // 2. Login usando la ruta real detectada: api/auth/login
    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    // Verificamos que el login responda 200
    $response->assertStatus(200);
    
    // Extraemos el token según el formato de tu API
    $token = $response->json('token') ?? $response->json('data.token') ?? $response->json('access_token');

    // 3. Logout usando la ruta real: api/auth/logout
    $this->withHeader('Authorization', 'Bearer ' . $token)
         ->postJson('/api/auth/logout')
         ->assertStatus(200);
});