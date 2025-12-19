<?php

namespace Database\Factories;

use App\Models\Medico;
use App\Models\User;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    protected $model = Medico::class;

    public function definition()
    {
        return [
            'usuario_id' => User::factory(),
            'especialidad_id' => Especialidad::factory(),
            'licencia_medica' => $this->faker->bothify('CMP-####'),
            'telefono_consultorio' => $this->faker->phoneNumber(),
            'biografia' => $this->faker->sentence(),
        ];
    }
}
