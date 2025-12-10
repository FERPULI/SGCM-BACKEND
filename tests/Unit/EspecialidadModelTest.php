<?php

use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses(Tests\TestCase::class);

test('una especialidad retiene nombre y descripcion en memoria', function () {
    $especialidad = Especialidad::make([
        'nombre' => 'Cardiología',
        'descripcion' => 'Especialidad dedicada al corazón y sistema circulatorio',
    ]);

    expect($especialidad->nombre)->toBe('Cardiología')
        ->and($especialidad->descripcion)->toBe('Especialidad dedicada al corazón y sistema circulatorio');
});

test('la relacion medicos existe como hasMany (definicion de relacion)', function () {
    $especialidad = new Especialidad();

    // Primera verificación: la relación devuelve HasMany
    expect($especialidad->medicos())->toBeInstanceOf(HasMany::class);

    // Segunda verificación: el modelo relacionado es Medico
    expect($especialidad->medicos()->getRelated())->toBeInstanceOf(Medico::class);
});

test('el id de la especialidad se trata como entero en memoria', function () {
    $especialidad = new Especialidad();
    $especialidad->id = "7";

    expect($especialidad->id)->toBe(7);
});
