<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    protected $model = Cita::class;

    public function definition(): array
    {
        return [
            'medico_id' => Medico::factory(),
            'paciente_id' => Paciente::factory(),
            'fecha_hora_inicio' => now()->addDay()->setHour(10),
            'fecha_hora_fin' => now()->addDay()->setHour(11),
            'estado' => 'programada',
            // ❌ 'motivo' ELIMINADO (no existe en la tabla)
        ];
    }
}
