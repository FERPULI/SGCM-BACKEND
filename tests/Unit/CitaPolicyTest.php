<?php

use App\Models\Cita;
use App\Models\User;
use App\Policies\CitaPolicy;

it('permite ver una cita al paciente propietario', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(true);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(false);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->paciente = (object) ['id' => 10];

    $cita = new Cita(['paciente_id' => 10]);

    expect((new CitaPolicy())->view($user, $cita))->toBeTrue();
});

it('no permite ver una cita si el paciente no es propietario', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(true);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(false);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->paciente = (object) ['id' => 20];

    $cita = new Cita(['paciente_id' => 10]);

    expect((new CitaPolicy())->view($user, $cita))->toBeFalse();
});

it('permite actualizar la cita al médico propietario', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(false);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(true);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->medico = (object) ['id' => 55];

    $cita = new Cita(['medico_id' => 55]);

    expect((new CitaPolicy())->update($user, $cita))->toBeTrue();
});

it('no permite eliminar cita si no es medico ni paciente', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(false);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(true);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->medico = (object) ['id' => 88];

    $cita = new Cita(['medico_id' => 55]);

    expect((new CitaPolicy())->delete($user, $cita))->toBeFalse();
});
