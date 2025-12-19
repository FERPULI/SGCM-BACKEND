<?php

namespace Database\Factories;

use App\Models\HistorialMedico;
use App\Models\Paciente;
use App\Models\Cita;
use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialMedicoFactory extends Factory
{
    protected $model = HistorialMedico::class;

    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            'medico_id'   => Medico::factory(),
            'cita_id'     => Cita::factory(),
            'diagnostico' => $this->faker->sentence(),
        ];
    }
}
