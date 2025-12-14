<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('update user request falla si email es invalido', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    $this->putJson("/api/users/{$user->id}", [
        'email' => 'correo-invalido'
    ])->assertStatus(422);
});
