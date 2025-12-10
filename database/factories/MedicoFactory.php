<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\User;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    protected $model = Medico::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'especialidad_id' => Especialidad::factory(),
            'licencia_medica' => strtoupper($this->faker->bothify('CMP-####')),
            'telefono_consultorio' => $this->faker->numerify('01######'),
            'biografia' => $this->faker->sentence(),
        ];
    }
}
