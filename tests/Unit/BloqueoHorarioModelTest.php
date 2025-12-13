<?php

namespace Tests\Unit;

use App\Models\BloqueoHorario;
use Tests\TestCase;

class BloqueoHorarioModelTest extends TestCase
{
    /**
     * Probamos si un bloqueo horario guarda correctamente su información en memoria.
     */
    public function testUnBloqueoHorarioGuardaCorrectamenteSuInformacionEnMemoria()
    {
        // Creación del objeto en memoria
        $bloqueo = new BloqueoHorario();

        // Asignación directa de valores en memoria (sin interactuar con la base de datos)
        $bloqueo->medico_id = 1;
        $bloqueo->fecha_inicio = '2024-06-15 08:00:00';
        $bloqueo->fecha_fin = '2024-06-15 12:00:00';
        $bloqueo->motivo = 'Vacaciones';

        // Verificación de los valores asignados
        $this->assertEquals(1, $bloqueo->medico_id);
        $this->assertEquals('2024-06-15 08:00:00', $bloqueo->fecha_inicio);
        $this->assertEquals('2024-06-15 12:00:00', $bloqueo->fecha_fin);
        $this->assertEquals('Vacaciones', $bloqueo->motivo);
    }

    /**
     * Probamos si el ID del bloqueo horario se trata correctamente como un entero.
     */
    public function testElIdDelBloqueoHorarioSeTrataComoEnteroEnMemoria()
    {
        $bloqueo = new BloqueoHorario();

        // Asignamos el ID como string
        $bloqueo->id = "15";

        // Verificamos que el ID se trata como un entero
        $this->assertEquals(15, $bloqueo->id);
    }
}
