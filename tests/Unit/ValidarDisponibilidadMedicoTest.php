<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Fake usado para simular el modelo Medico.
 */
class FakeMedico
{
    public array $citas = [];
    public array $bloqueos = [];
    public string $hora_inicio = '08:00';
    public string $hora_fin = '17:00';
}

/**
 * Servicio que valida disponibilidad del médico
 */
class ValidarDisponibilidadMedico
{
    public function validar(FakeMedico $medico, string $inicio, string $fin): bool
    {
        $ini = Carbon::parse($inicio);
        $fi  = Carbon::parse($fin);

        // Validación de horario laboral
        $hInicio = Carbon::parse($ini->format('Y-m-d') . ' ' . $medico->hora_inicio);
        $hFin    = Carbon::parse($ini->format('Y-m-d') . ' ' . $medico->hora_fin);

        if ($ini->lt($hInicio) || $fi->gt($hFin)) {
            return false;
        }

        // Validación de citas ocupadas
        foreach ($medico->citas as $cita) {
            $citaIni = Carbon::parse($cita['inicio']);
            $citaFin = Carbon::parse($cita['fin']);

            if ($ini->between($citaIni, $citaFin) || $fi->between($citaIni, $citaFin)) {
                return false;
            }
        }

        // Validación de bloqueos
        foreach ($medico->bloqueos as $bloq) {
            $bloqIni = Carbon::parse($bloq['inicio']);
            $bloqFin = Carbon::parse($bloq['fin']);

            if ($ini->between($bloqIni, $bloqFin) || $fi->between($bloqIni, $bloqFin)) {
                return false;
            }
        }

        return true;
    }
}

class ValidarDisponibilidadMedicoTest extends TestCase
{
    #[Test]
    public function valida_correctamente_cuando_el_medico_esta_disponible()
    {
        $medico = new FakeMedico();
        $servicio = new ValidarDisponibilidadMedico();

        $resultado = $servicio->validar(
            $medico,
            '2025-06-01 10:00',
            '2025-06-01 10:30'
        );

        $this->assertTrue($resultado);
    }

    #[Test]
    public function falla_cuando_el_medico_tiene_cita_ocupada()
    {
        $medico = new FakeMedico();

        $medico->citas = [
            [
                'inicio' => '2025-06-01 10:00',
                'fin'    => '2025-06-01 11:00',
            ],
        ];

        $servicio = new ValidarDisponibilidadMedico();

        $resultado = $servicio->validar(
            $medico,
            '2025-06-01 10:15',
            '2025-06-01 10:45'
        );

        $this->assertFalse($resultado);
    }

    #[Test]
    public function falla_cuando_el_medico_tiene_bloqueo()
    {
        $medico = new FakeMedico();

        $medico->bloqueos = [
            [
                'inicio' => '2025-06-01 14:00',
                'fin'    => '2025-06-01 15:00',
            ],
        ];

        $servicio = new ValidarDisponibilidadMedico();

        $resultado = $servicio->validar(
            $medico,
            '2025-06-01 14:30',
            '2025-06-01 14:50'
        );

        $this->assertFalse($resultado);
    }

    #[Test]
    public function falla_cuando_esta_fuera_del_horario_laboral()
    {
        $medico = new FakeMedico();

        $servicio = new ValidarDisponibilidadMedico();

        $resultado = $servicio->validar(
            $medico,
            '2025-06-01 07:30',
            '2025-06-01 08:00'
        );

        $this->assertFalse($resultado);
    }
}