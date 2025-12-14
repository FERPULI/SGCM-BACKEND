<?php

namespace Tests\Feature\Resources;

use App\Http\Resources\MedicoResource;
use App\Models\Medico;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('medico resource devuelve estructura correcta', function () {

    $medico = Medico::factory()->create();

    $resource = (new MedicoResource($medico))->toArray(request());

    expect($resource)->toHaveKeys([
        'id_medico',
        'id_usuario',
        'nombre_completo',
        'email',
        'licencia_medica',
        'telefono_consultorio',
        'biografia',
        'especialidad',
        'estadisticas',
    ]);

    expect($resource['especialidad'])->toHaveKeys([
        'id',
        'nombre',
    ]);

    expect($resource['estadisticas'])->toHaveKeys([
        'citas_totales',
        'citas_completadas',
        'citas_pendientes',
        'pacientes_atendidos',
    ]);
});
