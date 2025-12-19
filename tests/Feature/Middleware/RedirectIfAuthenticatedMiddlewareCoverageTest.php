<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;

it('redirect if authenticated permite acceso cuando no hay login', function () {

    Route::middleware(RedirectIfAuthenticated::class)
        ->get('/_test-guest', function () {
            return response()->json(['guest' => true]);
        });

    $this->getJson('/_test-guest')
         ->assertOk()
         ->assertJson(['guest' => true]);
});

it('redirect if authenticated redirige usuario autenticado', function () {

    Route::middleware(RedirectIfAuthenticated::class)
        ->get('/_test-guest-auth', function () {
            return response()->json(['guest' => true]);
        });

    $user = User::factory()->create();

    $this->actingAs($user)
         ->getJson('/_test-guest-auth')
         ->assertStatus(302);
});
