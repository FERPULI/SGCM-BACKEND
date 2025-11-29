<?php

use App\Models\Medico;

test('el modelo Medico retiene la información profesional correctamente', function () {
    $medico = new Medico();
    $medico->nombre = 'Dr. Gregory House';
    $medico->especialidad = 'Diagnóstico'; 
    $medico->telefono = '999888777';

    expect($medico->nombre)->toBe('Dr. Gregory House')
        ->and($medico->especialidad)->toBe('Diagnóstico');
});

test('el modelo Medico convierte automáticamente los IDs a números', function () {
    $medico = new Medico();
    
    // Le pasamos un string "50"
    $medico->id = "50";
    
    // VERIFICACIÓN EXITOSA:
    // Laravel hace "Type Casting" y lo convierte a entero (int) 50.
    // Esto prueba que el sistema sanea los datos.
    expect($medico->id)->toBe(50);
});