<?php

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses(Tests\TestCase::class);

test('un paciente guarda correctamente su informacion personal', function () {
    $paciente = new Paciente([
        'telefono' => '987654321',
        'direccion' => 'Av. Siempre Viva 742',
        'fecha_nacimiento' => '1995-06-15',
        'genero' => 'M',
    ]);

    expect($paciente->telefono)->toBe('987654321')
        ->and($paciente->direccion)->toBe('Av. Siempre Viva 742')
        ->and($paciente->fecha_nacimiento->toDateString())->toBe('1995-06-15')
        ->and($paciente->genero)->toBe('M');
});
