<?php

use App\Models\Cita;

test('el modelo Cita puede retener datos en memoria sin tocar la BD', function () {
    // 1. Instanciamos el modelo (Esto solo ocurre en la RAM del equipo)
    $cita = new Cita();

    // 2. Asignamos valores manualmente
    // Al hacerlo así, "saltamos" la protección del desarrollador sin editar su archivo.
    $cita->paciente_id = 1;
    $cita->medico_id = 5;
    $cita->motivo_consulta = 'Revision General'; // Asegúrate que este nombre coincida con tu columna real
    $cita->estado = 'pendiente';

    // 3. Verificamos que los datos están ahí
    // Esto prueba que el OBJETO funciona, sin probar la BASE DE DATOS.
    expect($cita->motivo_consulta)->toBe('Revision General')
        ->and($cita->estado)->toBe('pendiente')
        ->and($cita->medico_id)->toBe(5);
});