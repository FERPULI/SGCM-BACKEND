<?php

use App\Models\User;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['auth', CheckRole::class . ':admin'])
        ->get('/test-role', fn () => response()->json(['ok']));
});

it('middleware check role bloquea si el rol no coincide', function () {
    $user = User::factory()->create([
        'rol' => 'paciente',
    ]);

    $response = $this->actingAs($user)->get('/test-role');

    $response->assertStatus(403);
});

it('middleware check role permite si el rol coincide', function () {
    $user = User::factory()->create([
        'rol' => 'admin',
    ]);

    $response = $this->actingAs($user)->get('/test-role');

    $response->assertStatus(200);
});
