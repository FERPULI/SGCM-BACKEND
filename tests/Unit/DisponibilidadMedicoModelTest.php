<?php

use App\Models\DisponibilidadMedico;

// Entorno mínimo para Eloquent en memoria (sin BD)
uses(Tests\TestCase::class);

test('una disponibilidad medica guarda correctamente su informacion en memoria', function () {
    $disponibilidad = new DisponibilidadMedico();

    // Asignación directa en memoria
    $disponibilidad->dia = 'Lunes';
    $disponibilidad->hora_inicio = '08:00';
    $disponibilidad->hora_fin = '12:00';
    $disponibilidad->medico_id = 1;

    expect($disponibilidad->dia)->toBe('Lunes')
        ->and($disponibilidad->hora_inicio)->toBe('08:00')
        ->and($disponibilidad->hora_fin)->toBe('12:00')
        ->and($disponibilidad->medico_id)->toBe(1);
});

test('el id de la disponibilidad se trata como entero en memoria', function () {
    $disponibilidad = new DisponibilidadMedico();

    // Simulamos asignación externa
    $disponibilidad->id = "9";

    expect($disponibilidad->id)->toBe(9);
});
