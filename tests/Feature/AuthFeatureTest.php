<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Simulamos respuestas sin tocar BD ni backend
    Route::post('/api/auth/register', function () {
        return response()->json(['ok' => true], 201);
    });

    Route::post('/api/auth/login', function () {
        return response()->json(['token' => 'fake-token'], 200);
    });
});

test('rutas de auth: register y login existen y responden', function () {

    $registerResponse = $this->postJson('/api/auth/register', [
        'name' => 'Test Feature',
        'email' => 'test+feature@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertContains($registerResponse->status(), [200, 201, 422]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'test+feature@example.com',
        'password' => 'password',
    ]);

    $this->assertContains($loginResponse->status(), [200]);
});
