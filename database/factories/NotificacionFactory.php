<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mensaje' => $this->faker->sentence(),
            'leido' => false,
        ];
    }
}
