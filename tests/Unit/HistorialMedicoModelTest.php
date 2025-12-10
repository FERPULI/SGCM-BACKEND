<?php

use App\Models\HistorialMedico;

test('un historial medico guarda correctamente su informacion en memoria', function () {

    $historial = new HistorialMedico();

    $historial->diagnostico = 'Hipertensión arterial';
    $historial->tratamiento = 'Medicamento diario';
    $historial->observaciones = 'Control mensual';
    $historial->fecha = '2024-06-10';

    expect($historial->diagnostico)->toBe('Hipertensión arterial')
        ->and($historial->tratamiento)->toBe('Medicamento diario')
        ->and($historial->observaciones)->toBe('Control mensual')
        ->and($historial->fecha)->toBe('2024-06-10');
});
