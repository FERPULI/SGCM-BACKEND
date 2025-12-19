<?php

use App\Http\Middleware\Authenticate;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;

it('authenticate middleware bloquea usuario no autenticado', function () {

    // Definimos la ruta de prueba con el middleware
    Route::middleware(Authenticate::class)
        ->get('/_auth-test', function () {
            return response()->json(['ok' => true]);
        });

    // Desactivamos el manejo automático de excepciones para capturar la excepción nosotros mismos
    $this->withoutExceptionHandling();

    // Esperamos la excepción de autenticación
    $this->expectException(AuthenticationException::class);

    /**
     * CAMBIO CLAVE: Usamos getJson() en lugar de get().
     * Esto añade automáticamente la cabecera 'Accept: application/json'.
     * De esta forma, el middleware sabe que no debe intentar redirigir a 'login',
     * sino simplemente lanzar la excepción de autenticación.
     */
    $this->getJson('/_auth-test');
});

it('authenticate middleware permite acceso a usuario autenticado', function () {

    Route::middleware(Authenticate::class)
        ->get('/_auth-test-ok', function () {
            return response()->json(['ok' => true]);
        });

    $user = User::factory()->create();

    // En peticiones autenticadas también es buena práctica usar getJson
    $this->actingAs($user)
         ->getJson('/_auth-test-ok')
         ->assertOk()
         ->assertJson(['ok' => true]);
});