<?php

use App\Models\User;
use App\Models\Paciente;

it('it ejecuta relaciones de modelo paciente y user', function () {
    // 1. Creamos el usuario
    $user = User::factory()->create([
        'rol' => 'paciente'
    ]);

    // 2. Creamos el paciente asociado a ese usuario
    // Forzamos solo el uso de 'usuario_id'
    $paciente = Paciente::factory()->create([
        'usuario_id' => $user->id
    ]);

    // 3. Verificaciones de relación
    expect($paciente->user)->toBeInstanceOf(User::class)
        ->and($paciente->user->id)->toBe($user->id);
        
    expect($user->paciente)->toBeInstanceOf(Paciente::class)
        ->and($user->paciente->id)->toBe($paciente->id);
});