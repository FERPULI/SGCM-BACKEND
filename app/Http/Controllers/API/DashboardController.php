<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Cita;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        // 1. TARJETAS SUPERIORES
        $totalPacientes = Paciente::count();
        $totalMedicos   = Medico::count();
        // Citas que ocurren HOY (usamos la fecha del servidor, ojo con la zona horaria que configuramos antes)
        $citasHoy       = Cita::whereDate('fecha_hora_inicio', Carbon::today())->count();
        $pendientes     = Cita::where('estado', 'programada')->count();

        // 2. ACTIVIDAD DEL SISTEMA (Cálculos de porcentajes)
        $totalCitas   = Cita::count();
        $completadas  = Cita::where('estado', 'completada')->count();
        $canceladas   = Cita::where('estado', 'cancelada')->count();

        // Evitamos división por cero si no hay citas
        $tasaCompletacion = $totalCitas > 0 ? round(($completadas / $totalCitas) * 100) : 0;
        $tasaCancelacion  = $totalCitas > 0 ? round(($canceladas / $totalCitas) * 100) : 0;

        // 3. ESTADÍSTICAS RÁPIDAS
        $citasMes       = Cita::whereMonth('fecha_hora_inicio', Carbon::now()->month)->count();
        // Pacientes registrados este mes
        $nuevosPacientes = Paciente::whereMonth('created_at', Carbon::now()->month)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'cards' => [
                    'pacientes' => $totalPacientes,
                    'medicos'   => $totalMedicos,
                    'citas_hoy' => $citasHoy,
                    'pendientes'=> $pendientes
                ],
                'activity' => [
                    'completadas'       => $completadas,
                    'tasa_completacion' => $tasaCompletacion,
                    'tasa_cancelacion'  => $tasaCancelacion
                ],
                'quick' => [
                    'total_citas'      => $totalCitas,
                    'citas_mes'        => $citasMes,
                    'nuevos_pacientes' => $nuevosPacientes
                ]
            ]
        ]);
    }
}