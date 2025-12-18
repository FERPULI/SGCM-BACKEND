<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Obtiene todas las estadísticas para el panel de administración.
     */
    public function getStats(Request $request)
    {
        // --- 1. Fechas de Referencia ---
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // --- 2. Estadísticas de Usuarios ---
        $totalPacientes = Paciente::count(); // Más preciso usar el modelo Paciente
        $totalMedicos = Medico::count();     // Más preciso usar el modelo Medico
        $nuevosUsuariosEsteMes = User::where('created_at', '>=', $startOfMonth)->count();

        // --- 3. Estadísticas de Citas (Globales) ---
        
        // A. Citas Hoy
        $citasHoy = Cita::whereDate('fecha_hora_inicio', $today)->count();
        
        // B. Total Histórico
        $totalCitas = Cita::count();

        // C. Desglose por Estado
        $citasPendientes = Cita::where('estado', 'programada')->count();
        $citasConfirmadas = Cita::where('estado', 'confirmada')->count();
        $citasCompletadas = Cita::where('estado', 'completada')->count();
        $citasCanceladas = Cita::where('estado', 'cancelada')->count();

        // D. Citas "Activas" (Pendientes + Confirmadas) -> Para tu requerimiento específico
        $citasActivas = $citasPendientes + $citasConfirmadas;
        
        // E. Este Mes
        $citasEsteMes = Cita::where('fecha_hora_inicio', '>=', $startOfMonth)->count();

        // --- 4. Cálculo de Tasas (KPIs) ---
        $totalCitasGestionadas = $citasCompletadas + $citasCanceladas;
        
        $tasaCompletacion = ($totalCitasGestionadas > 0) 
            ? round(($citasCompletadas / $totalCitasGestionadas) * 100, 2) 
            : 0;
            
        $tasaCancelacion = ($totalCitasGestionadas > 0) 
            ? round(($citasCanceladas / $totalCitasGestionadas) * 100, 2) 
            : 0;

        // --- 5. Citas Recientes (Para la tabla inferior del dashboard) ---
        $citasRecientes = Cita::with(['paciente.user', 'medico.user', 'medico.especialidad'])
            ->orderBy('fecha_hora_inicio', 'desc')
            ->limit(5)
            ->get();

        // --- 6. Ensamblar Respuesta JSON ---
        return response()->json([
            // Tarjetas Superiores (KPIs)
            'totalPacientes' => $totalPacientes,
            'totalMedicos' => $totalMedicos,
            'citasHoy' => $citasHoy,
            'citasActivas' => $citasActivas, // <--- ¡NUEVO!
            'citasPendientes' => $citasPendientes,
            'citasConfirmadas' => $citasConfirmadas,

            // Sección "Actividad del Sistema"
            'citasCompletadas' => $citasCompletadas,
            'citasCanceladas' => $citasCanceladas,
            'tasaCompletacion' => $tasaCompletacion,
            'tasaCancelacion' => $tasaCancelacion,

            // Sección "Estadísticas Rápidas"
            'totalCitas' => $totalCitas,
            'citasEsteMes' => $citasEsteMes,
            'nuevosUsuarios' => $nuevosUsuariosEsteMes,
            
            // Sección "Citas Recientes"
            'citasRecientes' => CitaResource::collection($citasRecientes),
        ]);
    }
}