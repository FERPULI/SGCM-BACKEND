<?php

use App\Models\BloqueoHorario;

/*
|--------------------------------------------------------------------------
| Inicialización del entorno de pruebas
| Permite usar Eloquent en memoria (sin base de datos)
|--------------------------------------------------------------------------
*/
uses(Tests\TestCase::class);

test('un bloqueo horario guarda correctamente su informacion en memoria', function () {

    $bloqueo = new BloqueoHorario();

    // ✅ Asignación directa en memoria
    // Evitamos make() porque el modelo NO expone estos campos en $fillable
    $bloqueo->medico_id    = 1;
    $bloqueo->fecha_inicio = '2024-06-15 08:00:00';
    $bloqueo->fecha_fin    = '2024-06-15 12:00:00';
    $bloqueo->motivo       = 'Vacaciones';

    // ✅ Verificación estrictamente en memoria
    expect($bloqueo->medico_id)->toBe(1)
        ->and($bloqueo->fecha_inicio)->toBe('2024-06-15 08:00:00')
        ->and($bloqueo->fecha_fin)->toBe('2024-06-15 12:00:00')
        ->and($bloqueo->motivo)->toBe('Vacaciones');
});

test('el id del bloqueo horario se trata como entero en memoria', function () {

    $bloqueo = new BloqueoHorario();

    // Asignación como string
    $bloqueo->id = "15";

    // Laravel/PHP lo maneja como entero
    expect($bloqueo->id)->toBe(15);
});
