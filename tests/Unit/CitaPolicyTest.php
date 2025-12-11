<?php

use App\Models\Cita;
use App\Models\User;
use App\Policies\CitaPolicy;

it('permite ver una cita al paciente propietario', function () {

    // Simulamos un usuario con rol "paciente"
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(true);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(false);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    // Mock relación paciente
    $user->paciente = (object)['id' => 10];

    // Cita asignada al paciente 10
    $cita = new Cita(['paciente_id' => 10]);

    $policy = new CitaPolicy();

    expect($policy->view($user, $cita))->toBeTrue();
});

it('no permite ver una cita si el paciente no es el propietario', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(true);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(false);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->paciente = (object)['id' => 20]; // ID diferente

    $cita = new Cita(['paciente_id' => 10]);

    $policy = new CitaPolicy();

    expect($policy->view($user, $cita))->toBeFalse();
});

it('permite actualizar la cita al médico propietario', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(false);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(true);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->medico = (object)['id' => 55];

    $cita = new Cita(['medico_id' => 55]);

    $policy = new CitaPolicy();

    expect($policy->update($user, $cita))->toBeTrue();
});

it('no permite eliminar una cita cuando el usuario no es el médico/paciente asociado', function () {

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('hasRole')->with('paciente')->andReturn(false);
    $user->shouldReceive('hasRole')->with('medico')->andReturn(true);
    $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

    $user->medico = (object)['id' => 88];

    $cita = new Cita(['medico_id' => 55]); // IDs diferentes

    $policy = new CitaPolicy();

    expect($policy->delete($user, $cita))->toBeFalse();
});
