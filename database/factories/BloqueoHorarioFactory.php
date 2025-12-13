<?php

namespace Database\Factories;

use App\Models\Medico;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BloqueoHorarioFactory extends Factory
{
    public function definition(): array
    {
        $inicio = now()->addDay()->setTime(10, 0);
        
        return [
            'medico_id' => Medico::factory(),
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addHour(),
            'motivo' => 'Reunión interna',
        ];
    }
}
