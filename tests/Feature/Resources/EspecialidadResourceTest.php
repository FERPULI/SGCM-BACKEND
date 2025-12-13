<?php

namespace Tests\Feature\Resources;

use App\Http\Resources\EspecialidadResource;
use App\Models\Especialidad;
use Tests\TestCase;

class EspecialidadResourceTest extends TestCase
{
    public function test_formato_resource_es_correcto()
    {
        $especialidad = Especialidad::factory()->make();

        $resource = (new EspecialidadResource($especialidad))->toArray(request());

        $this->assertArrayHasKey('id', $resource);
        $this->assertArrayHasKey('nombre', $resource);
        $this->assertArrayHasKey('descripcion', $resource);
    }
}
