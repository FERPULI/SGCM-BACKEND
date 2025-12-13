<?php

use App\Http\Resources\EspecialidadResource;
use App\Models\Especialidad;

test('especialidad resource devuelve estructura correcta', function () {
    $especialidad = new Especialidad([
        'id' => 1,
        'nombre' => 'Cardiología'
    ]);

    $resource = (new EspecialidadResource($especialidad))->toArray(request());

    expect($resource)->toHaveKeys(['id', 'nombre']);
});
