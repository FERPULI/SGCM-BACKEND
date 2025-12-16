<?php

namespace Tests\Feature\Policies;

use App\Models\Cita;
use App\Models\User;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaPolicyMedicoNoOwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_medico_no_propietario_no_puede_actualizar_cita(): void
    {
        // Paciente OWNER (requiere usuario_id)
        $pacienteOwnerUser = User::factory()->create(['rol' => 'paciente']);
        $pacienteOwner = Paciente::factory()->create([
            'usuario_id' => $pacienteOwnerUser->id,
        ]);

        // Médico OWNER de la cita
        $medicoOwnerUser = User::factory()->create(['rol' => 'medico']);
        $medicoOwner = Medico::factory()->create([
            'usuario_id' => $medicoOwnerUser->id,
        ]);

        // Médico NO owner (el que intenta actualizar)
        $medicoNoOwnerUser = User::factory()->create(['rol' => 'medico']);
        $medicoNoOwner = Medico::factory()->create([
            'usuario_id' => $medicoNoOwnerUser->id,
        ]);

        $cita = Cita::factory()->create([
            'medico_id' => $medicoOwner->id,
            'paciente_id' => $pacienteOwner->id,
        ]);

        $this->assertFalse(
            $medicoNoOwnerUser->can('update', $cita)
        );
    }
}
