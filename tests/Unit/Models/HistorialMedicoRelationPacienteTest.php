<?php

namespace Tests\Unit\Models;

use App\Models\HistorialMedico;
use Tests\TestCase;

class HistorialMedicoRelationPacienteTest extends TestCase
{
    public function test_historial_medico_pertenece_a_un_paciente(): void
    {
        $historial = new HistorialMedico();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $historial->paciente()
        );
    }
}
