<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['guest'])->get('/_guest_test', fn () => response()->json(['guest']));
});

it('permite acceso sin login', function () {
    $this->getJson('/_guest_test')->assertStatus(200);
});

it('redirige usuario autenticado', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/_guest_test')->assertStatus(302);
});
