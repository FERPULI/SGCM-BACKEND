<?php

use App\Models\User;

test('el modelo User retiene datos básicos en memoria', function () {
    // 1. Instanciamos
    $user = new User();
    
    // 2. Asignamos datos
    $user->name = 'Jenrry QA';
    $user->email = 'qa@empresa.com';
    
    // 3. Verificamos que los datos estén ahí
    expect($user->name)->toBe('Jenrry QA')
        ->and($user->email)->toBe('qa@empresa.com');
});

test('el modelo User NO debe mostrar la contraseña al convertirse en array/json', function () {
    // ESTA ES UNA PRUEBA DE SEGURIDAD CRÍTICA
    
    // 1. Creamos un usuario con contraseña
    $user = new User();
    $user->password = 'mi_password_secreto_123';
    
    // 2. Simulamos la conversión que hace Laravel al enviar datos a la API
    $datosVisibles = $user->toArray();

    // 3. Verificamos: ¡La contraseña NO debería estar en la lista!
    // Si esta prueba FALLA (Rojo), significa que hay un BUG de seguridad: 
    // el backend está enviando contraseñas visibles.
    expect($datosVisibles)->not->toHaveKey('password');
});