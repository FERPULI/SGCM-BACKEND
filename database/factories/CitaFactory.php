<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaFactory extends Factory
{
    public function definition(): array
    {
        $inicio = now()->addDay()->setTime(11, 0);

        return [
            'medico_id' => Medico::factory(),
            'paciente_id' => Paciente::factory(),
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(30),
            'estado' => 'programada',
            'motivo' => 'Consulta general',
        ];
    }
}
