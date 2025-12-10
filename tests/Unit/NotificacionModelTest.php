<?php

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ⚠️ Importante:
// Usamos Tests\TestCase para que Eloquent pueda inicializar
// relaciones y el contenedor de Laravel, SIN usar base de datos.
uses(Tests\TestCase::class);

test('una notificacion guarda correctamente su informacion en memoria', function () {
    // Creamos la instancia SOLO en memoria
    $notificacion = new Notificacion();

    // Asignación directa de atributos
    // No usamos create(), save() ni make() para evitar BD y fillable
    $notificacion->titulo = 'Cita confirmada';
    $notificacion->mensaje = 'Su cita ha sido confirmada con éxito';
    $notificacion->leido = false;

    // ✅ Verificación estrictamente en memoria
    expect($notificacion->titulo)->toBe('Cita confirmada')
        ->and($notificacion->mensaje)->toBe('Su cita ha sido confirmada con éxito')
        ->and($notificacion->leido)->toBeFalse();
});

test('una notificacion pertenece a un usuario (definicion de relacion)', function () {
    // No se necesita información persistida
    $notificacion = new Notificacion();

    // ✅ Se valida la definición de la relación, no los datos
    expect($notificacion->user())->toBeInstanceOf(BelongsTo::class)

        // El modelo relacionado debe ser User
        ->and($notificacion->user()->getRelated())->toBeInstanceOf(User::class);
});

test('el id de la notificacion se trata como entero en memoria', function () {
    $notificacion = new Notificacion();

    // Asignación en memoria como string
    $notificacion->id = "10";

    // ✅ Laravel/PHP lo trata como entero al acceder
    expect($notificacion->id)->toBe(10);
});
