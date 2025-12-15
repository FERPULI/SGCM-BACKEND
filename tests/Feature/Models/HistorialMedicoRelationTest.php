<?php

namespace Tests\Feature\Models;

use App\Models\HistorialMedico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('historial medico pertenece a un paciente', function () {

    $historial = new HistorialMedico();

    expect($historial->paciente())
        ->toBeInstanceOf(BelongsTo::class);
});
