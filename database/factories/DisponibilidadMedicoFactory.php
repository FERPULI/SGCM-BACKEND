<?php

namespace Database\Factories;

use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisponibilidadMedicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medico_id' => Medico::factory(),
            'dia_semana' => $this->faker->numberBetween(1, 6), // lunes a sábado
            'hora_inicio' => '08:00:00',
            'hora_fin' => '17:00:00',
        ];
    }
}
