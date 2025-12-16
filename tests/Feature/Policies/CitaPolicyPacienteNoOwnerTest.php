<?php

namespace Tests\Feature\Policies;

use App\Models\Cita;
use App\Models\User;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaPolicyPacienteNoOwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_paciente_no_propietario_no_puede_actualizar_cita(): void
    {
        // Paciente OWNER
        $pacienteOwnerUser = User::factory()->create(['rol' => 'paciente']);
        $pacienteOwner = Paciente::factory()->create([
            'usuario_id' => $pacienteOwnerUser->id,
        ]);

        // Paciente NO owner
        $pacienteNoOwnerUser = User::factory()->create(['rol' => 'paciente']);
        $pacienteNoOwner = Paciente::factory()->create([
            'usuario_id' => $pacienteNoOwnerUser->id,
        ]);

        // Médico de la cita
        $medicoUser = User::factory()->create(['rol' => 'medico']);
        $medico = Medico::factory()->create([
            'usuario_id' => $medicoUser->id,
        ]);

        $cita = Cita::factory()->create([
            'paciente_id' => $pacienteOwner->id,
            'medico_id' => $medico->id,
        ]);

        $this->assertFalse(
            $pacienteNoOwnerUser->can('update', $cita)
        );
    }
}
