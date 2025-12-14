<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('middleware authenticate bloquea acceso sin login', function () {

    $this->getJson('/api/users')
        ->assertStatus(401);
});
