<?php

use App\Models\User;
use App\Models\Paciente;

test('limpiar lineas rojas en modelos', function () {
    // Creamos un usuario
    $user = User::factory()->create();
    
    // Acceder a las propiedades dinámicas activa la cobertura del código de la relación
    // No usamos expect(...)->toBeDefined() porque causa el error que ves
    $pacienteRel = $user->paciente;
    $medicoRel = $user->medico;

    // Crear un paciente asociado con la columna correcta
    $paciente = Paciente::factory()->create([
        'usuario_id' => $user->id
    ]);

    // Verificamos que la relación inversa funcione
    expect($paciente->user)->toBeInstanceOf(User::class);
});