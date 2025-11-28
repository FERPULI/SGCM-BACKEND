<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'nombre' => 'Admin',
                'apellidos' => 'Demo',
                'password' => Hash::make('password123'),
                'rol' => 'admin',
                'activo' => true,
            ]
        );

        // Médico
        User::updateOrCreate(
            ['email' => 'medico@demo.test'],
            [
                'nombre' => 'Dr. Juan',
                'apellidos' => 'Pérez',
                'password' => Hash::make('password123'),
                'rol' => 'medico',
                'activo' => true,
            ]
        );

        // Recepción / agente
        User::updateOrCreate(
            ['email' => 'recepcion@demo.test'],
            [
                'nombre' => 'Recepcion',
                'apellidos' => 'Demo',
                'password' => Hash::make('password123'),
                'rol' => 'recepcion',
                'activo' => true,
            ]
        );

        // Paciente
        User::updateOrCreate(
            ['email' => 'paciente@demo.test'],
            [
                'nombre' => 'Paciente',
                'apellidos' => 'Demo',
                'password' => Hash::make('password123'),
                'rol' => 'paciente',
                'activo' => true,
            ]
        );
    }
}
