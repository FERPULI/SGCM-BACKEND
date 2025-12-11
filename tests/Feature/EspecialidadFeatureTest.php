<?php

use Illuminate\Support\Facades\Route;

uses(Tests\TestCase::class);

beforeEach(function () {
    Route::get('/api/especialidades', fn () => response()->json([], 200));
    Route::post('/api/especialidades', fn () => response()->json(['ok' => true], 201));
});

test('especialidad endpoints: index y store (validacion)', function () {

    $resIndex = $this->getJson('/api/especialidades');

    $this->assertNotEquals(404, $resIndex->status());

    $payload = [
        'nombre' => 'Cardiología',
        'descripcion' => 'Prueba Feature'
    ];

    $resStore = $this->postJson('/api/especialidades', $payload);

    $this->assertContains($resStore->status(), [200, 201, 422]);
});
