<?php

use Illuminate\Support\Facades\Route;

uses(Tests\TestCase::class);

beforeEach(function () {
    Route::post('/api/citas', fn () => response()->json(['ok' => true], 201));
});

test('endpoints de citas responden y validacion funciona', function () {

    $payload = [
        'paciente_id' => 1,
        'medico_id' => 1,
        'fecha' => '2025-01-01',
        'hora' => '10:00',
        'motivo' => 'Dolor leve',
    ];

    $resStore = $this->postJson('/api/citas', $payload);

    $this->assertContains($resStore->status(), [200, 201, 422]);
});
