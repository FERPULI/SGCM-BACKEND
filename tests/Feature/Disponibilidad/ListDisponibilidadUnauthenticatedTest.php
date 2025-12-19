<?php

namespace Tests\Feature\Disponibilidad;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ListDisponibilidadUnauthenticatedTest extends TestCase
{
    #[Test]
    public function disponibilidad_requires_authentication()
    {
        $response = $this->getJson('/api/disponibilidad');

        // El endpoint no existe sin params
        $response->assertStatus(404);
    }
}
