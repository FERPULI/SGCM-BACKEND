<?php

namespace Tests\Unit\Models;

use App\Models\Paciente;
use Tests\TestCase;

class PacienteAttributesTest extends TestCase
{
    public function test_paciente_reconoce_atributos_basicos(): void
    {
        $paciente = new Paciente([
            'telefono' => '999999999',
            'direccion' => 'Av. Principal 123',
        ]);

        $this->assertEquals('999999999', $paciente->telefono);
        $this->assertEquals('Av. Principal 123', $paciente->direccion);
    }
}
