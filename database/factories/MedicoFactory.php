<?php

namespace Database\Factories;

use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoFactory extends Factory
{
    protected $model = Medico::class;

    public function definition()
    {
        return [
            'usuario_id' => null, // SE SETEA DESDE EL TEST
            'especialidad_id' => null,
            'licencia_medica' => 'CMP-' . $this->faker->numberBetween(1000, 9999),
            'telefono_consultorio' => $this->faker->phoneNumber(),
            'biografia' => $this->faker->sentence(),
        ];
    }
}
