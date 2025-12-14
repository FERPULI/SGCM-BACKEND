<?php

namespace Tests\Feature\Resources;

use App\Http\Resources\CitaResource;
use App\Models\Cita;

test('cita resource devuelve estructura correcta', function () {

    $cita = new Cita([
        'id' => 1,
        'fecha_hora_inicio' => now(),
        'fecha_hora_fin' => now()->addHour(),
        'estado' => 'pendiente'
    ]);

    $resource = (new CitaResource($cita))->toArray(request());

    expect($resource)->toHaveKeys([
        'id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado'
    ]);
});
