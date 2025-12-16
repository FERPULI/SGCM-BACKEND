<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Cita;
use Illuminate\Support\Facades\DB; // Usaremos DB para insertar historiales directamente
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIEZA TOTAL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Especialidad::truncate();
        Medico::truncate();
        Paciente::truncate();
        Cita::truncate();
        DB::table('disponibilidad_medicos')->truncate();
        DB::table('historiales_medicos')->truncate(); // <--- Limpiamos historial también
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. CREAR ESPECIALIDADES
        $especialidadesNombres = [
            'Cardiología', 'Pediatría', 'Dermatología', 'Ginecología', 
            'Neurología', 'Traumatología', 'Oftalmología', 'Psiquiatría', 
            'Medicina General', 'Gastroenterología'
        ];
        
        $especialidadIds = [];
        foreach ($especialidadesNombres as $nombre) {
            $esp = Especialidad::create([
                'nombre' => $nombre, 
                'descripcion' => "Atención especializada y profesional en $nombre"
            ]);
            $especialidadIds[] = $esp->id;
        }

        // 3. CREAR ADMIN
        User::create([
            'nombre' => 'Super',
            'apellidos' => 'Admin',
            'email' => 'admin@system.com',
            'password' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => 1,
            'email_verified_at' => now(),
        ]);

        // 4. CREAR MÉDICOS Y DISPONIBILIDAD
        $nombres = ['Juan', 'Maria', 'Carlos', 'Laura', 'Pedro', 'Ana', 'Luis', 'Sofia', 'Jorge', 'Elena', 'Ricardo', 'Carmen', 'Fernando', 'Patricia', 'Daniel', 'Rosa', 'Miguel', 'Lucia', 'David', 'Isabel'];
        $apellidos = ['Perez', 'Gomez', 'Rodriguez', 'Fernandez', 'Lopez', 'Martinez', 'Sanchez', 'Romero', 'Diaz', 'Torres', 'Ruiz', 'Vargas', 'Castro', 'Morales', 'Herrera', 'Jimenez', 'Rojas', 'Mendoza', 'Flores', 'Castillo'];

        $medicosIds = [];

        for ($i = 0; $i < 20; $i++) {
            $user = User::create([
                'nombre' => $nombres[$i],
                'apellidos' => $apellidos[$i],
                'email' => strtolower($nombres[$i]) . '.' . strtolower($apellidos[$i]) . '@hospital.com',
                'password' => Hash::make('password'),
                'rol' => 'medico',
                'activo' => 1,
                'email_verified_at' => now(),
            ]);

            $medico = Medico::create([
                'usuario_id' => $user->id,
                'especialidad_id' => $especialidadIds[array_rand($especialidadIds)],
                'licencia_medica' => 'CMP-' . rand(10000, 99999),
                'telefono_consultorio' => '01-' . rand(200, 999) . '-' . rand(1000, 9999),
                'biografia' => 'Médico especialista certificado con amplia experiencia.',
            ]);
            $medicosIds[] = $medico->id;

            // Disponibilidad Lunes a Viernes
            for ($dia = 1; $dia <= 5; $dia++) {
                DB::table('disponibilidad_medicos')->insert([
                    'medico_id' => $medico->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => '08:00:00',
                    'hora_fin' => '14:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. CREAR PACIENTES
        $pacientesIds = [];
        $tiposSangre = ['O+', 'A+', 'B+', 'O-', 'A-', 'AB+'];
        $distritos = ['Miraflores', 'San Isidro', 'Lima', 'Surco', 'San Borja', 'Lince'];

        for ($i = 0; $i < 20; $i++) {
            $user = User::create([
                'nombre' => $nombres[19 - $i], 
                'apellidos' => $apellidos[19 - $i],
                'email' => 'paciente' . ($i + 1) . '@gmail.com',
                'password' => Hash::make('password'),
                'rol' => 'paciente',
                'activo' => 1,
                'email_verified_at' => now(),
            ]);

            $paciente = Paciente::create([
                'usuario_id' => $user->id,
                'fecha_nacimiento' => Carbon::now()->subYears(rand(18, 60)),
                'telefono' => '9' . rand(10000000, 99999999),
                'direccion' => 'Av. Principal ' . rand(100, 999) . ', ' . $distritos[array_rand($distritos)],
                'tipo_sangre' => $tiposSangre[array_rand($tiposSangre)],
                'alergias' => 'Ninguna',
            ]);
            $pacientesIds[] = $paciente->id;
        }

        // 6. CREAR CITAS
        $estados = ['programada', 'confirmada', 'completada', 'cancelada']; 
        $motivos = ['Chequeo General', 'Dolor de cabeza', 'Fiebre alta', 'Consulta de rutina', 'Alergia estacional', 'Dolor abdominal'];

        for ($i = 0; $i < 50; $i++) {
            $esPasada = rand(0, 1);
            
            if ($esPasada) {
                // Cita Pasada = COMPLETADA
                $fecha = Carbon::now()->subDays(rand(1, 60))->setHour(rand(9, 13))->setMinute(0);
                $estado = 'completada';
            } else {
                // Cita Futura
                $fecha = Carbon::now()->addDays(rand(1, 30))->setHour(rand(9, 13))->setMinute(0);
                $estado = 'programada';
            }

            if ($fecha->dayOfWeek == 0 || $fecha->dayOfWeek == 6) $fecha->addDays(2);

            Cita::create([
                'paciente_id' => $pacientesIds[array_rand($pacientesIds)],
                'medico_id' => $medicosIds[array_rand($medicosIds)],
                'fecha_hora_inicio' => $fecha,
                'fecha_hora_fin' => $fecha->copy()->addMinutes(30),
                'estado' => $estado,
                'motivo_consulta' => $motivos[array_rand($motivos)],
                'notas_paciente' => null,
            ]);
        }

        // ==============================================================
        // 7. (NUEVO) CREAR HISTORIAL MÉDICO PARA CITAS COMPLETADAS
        // ==============================================================
        
        $diagnosticos = [
            'Faringitis aguda', 'Gastroenteritis leve', 'Migraña tensional', 
            'Hipertensión controlada', 'Dermatitis atópica', 'Control anual saludable'
        ];
        $tratamientos = [
            'Reposo absoluto por 3 días', 'Dieta blanda y mucha hidratación', 
            'Reducción de estrés y analgésicos', 'Continuar medicación habitual', 
            'Crema tópica cada 8 horas', 'Ejercicio regular y dieta balanceada'
        ];

        // Buscamos todas las citas que acabamos de crear con estado 'completada'
        $citasCompletadas = Cita::where('estado', 'completada')->get();

        foreach($citasCompletadas as $cita) {
            DB::table('historiales_medicos')->insert([
                'cita_id' => $cita->id,
                'paciente_id' => $cita->paciente_id,
                'medico_id' => $cita->medico_id,
                'diagnostico' => $diagnosticos[array_rand($diagnosticos)],
                'tratamiento' => $tratamientos[array_rand($tratamientos)],
                'recetas' => 'Paracetamol 500mg, Ibuprofeno 400mg',
                'notas_privadas_medico' => 'Paciente reacciona bien al tratamiento inicial.',
                'created_at' => $cita->fecha_hora_fin, // Se crea al terminar la cita
                'updated_at' => $cita->fecha_hora_fin,
            ]);
        }
    }
}