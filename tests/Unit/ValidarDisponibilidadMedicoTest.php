<?php

namespace Tests\Unit;

use App\Rules\ValidarDisponibilidadMedico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class ValidarDisponibilidadMedicoTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function valida_correctamente_cuando_el_medico_esta_disponible()
    {
        $this->mockearModelos(
            tieneBloqueo: false,
            tieneDisponibilidad: true,
            tieneCita: false
        );

        $rule = new ValidarDisponibilidadMedico;

        $validator = Validator::make([
            'fecha_hora_inicio' => '2025-01-15 10:00:00',
            'fecha_hora_fin'    => '2025-01-15 10:30:00',
            'medico_id'         => 1,
        ], [
            'fecha_hora_inicio' => [$rule],
        ]);

        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function falla_cuando_el_medico_tiene_cita_ocupada()
    {
        $this->mockearModelos(
            tieneBloqueo: false,
            tieneDisponibilidad: true,
            tieneCita: true
        );

        $rule = new ValidarDisponibilidadMedico;

        $validator = Validator::make([
            'fecha_hora_inicio' => '2025-01-15 10:00:00',
            'fecha_hora_fin'    => '2025-01-15 10:30:00',
            'medico_id'         => 1,
        ], [
            'fecha_hora_inicio' => [$rule],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            "El médico ya tiene una cita programada en este horario.",
            $validator->errors()->first('fecha_hora_inicio')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function falla_cuando_el_medico_tiene_bloqueo()
    {
        $this->mockearModelos(
            tieneBloqueo: true,
            tieneDisponibilidad: true,
            tieneCita: false
        );

        $rule = new ValidarDisponibilidadMedico;

        $validator = Validator::make([
            'fecha_hora_inicio' => '2025-01-15 10:00:00',
            'fecha_hora_fin'    => '2025-01-15 10:30:00',
            'medico_id'         => 1,
        ], [
            'fecha_hora_inicio' => [$rule],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            "El médico ha bloqueado este horario por motivos personales o emergencia.",
            $validator->errors()->first('fecha_hora_inicio')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function falla_cuando_esta_fuera_del_horario_laboral()
    {
        $this->mockearModelos(
            tieneBloqueo: false,
            tieneDisponibilidad: false,
            tieneCita: false
        );

        $rule = new ValidarDisponibilidadMedico;

        $validator = Validator::make([
            'fecha_hora_inicio' => '2025-01-15 22:00:00',
            'fecha_hora_fin'    => '2025-01-15 22:30:00',
            'medico_id'         => 1,
        ], [
            'fecha_hora_inicio' => [$rule],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertEquals(
            "El médico no está disponible en este día u horario.",
            $validator->errors()->first('fecha_hora_inicio')
        );
    }

    private function mockearModelos(bool $tieneBloqueo, bool $tieneDisponibilidad, bool $tieneCita)
    {
        // Mock Bloqueos
        $mockBloqueo = Mockery::mock('alias:App\Models\BloqueoHorario');
        $mockBloqueo->shouldReceive('where->where->exists')
            ->andReturn($tieneBloqueo);

        // Mock Disponibilidad
        $mockDisp = Mockery::mock('alias:App\Models\DisponibilidadMedico');
        $mockDisp->shouldReceive('where->where->where->where->first')
            ->andReturn($tieneDisponibilidad ? (object)['id' => 1] : null);

        // Mock Citas
        $mockCita = Mockery::mock('alias:App\Models\Cita');
        $mockCita->shouldReceive('where->where->where->exists')
            ->andReturn($tieneCita);
    }
}
