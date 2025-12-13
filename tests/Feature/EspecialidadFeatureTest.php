<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EspecialidadFeatureTest extends TestCase
{
    public function test_index_especialidades_responde()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/especialidades');

        $response->assertStatus(200);
    }

    public function test_store_especialidad_responde()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/especialidades', [
            'nombre' => 'Neurología',
            'descripcion' => 'Sistema nervioso',
        ]);

        $response->assertStatus(201);
    }
}
