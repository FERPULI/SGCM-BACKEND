<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rutas protegidas rechazan usuarios no autenticados', function () {

    $response = $this->getJson('/api/dashboard-stats');

    $response->assertStatus(401);
});
