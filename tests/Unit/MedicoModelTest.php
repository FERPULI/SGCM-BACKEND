<?php

use App\Models\Medico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses(Tests\TestCase::class);

test('un medico guarda correctamente su informacion profesional', function () {
    $medico = new Medico([
        'licencia_medica' => 'CMP-123456',
        'telefono_consultorio' => '999888777',
        'biografia' => 'Médico especialista en cardiología'
    ]);

    expect($medico->licencia_medica)->toBe('CMP-123456')
        ->and($medico->telefono_consultorio)->toBe('999888777')
        ->and($medico->biografia)->toBe('Médico especialista en cardiología');
});

test('un medico pertenece a un usuario (definicion de relacion)', function () {
    $medico = new Medico();

    expect($medico->user())
        ->toBeInstanceOf(BelongsTo::class);
});

test('un medico pertenece a una especialidad (definicion de relacion)', function () {
    $medico = new Medico();

    expect($medico->especialidad())
        ->toBeInstanceOf(BelongsTo::class);
});

test('un medico puede tener muchas citas (definicion de relacion)', function () {
    $medico = new Medico();

    expect($medico->citas())
        ->toBeInstanceOf(HasMany::class);
});

test('el id del medico es tratado como entero', function () {
    $medico = new Medico();

    $medico->id = "15";

    expect($medico->id)->toBe(15);
});
