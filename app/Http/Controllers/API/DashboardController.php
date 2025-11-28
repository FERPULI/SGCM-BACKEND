<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // --- 2. Estadísticas de Usuarios (Tu petición) ---
        $totalPacientes = User::where('rol', 'paciente')->count();
        $totalMedicos = User::where('rol', 'medico')->count();
        $nuevosUsuariosEsteMes = User::where('created_at', '>=', $startOfMonth)->count();

        // --- 3. Estadísticas de Citas (Globales) ---
        $citasHoy = Cita::whereDate('fecha_hora_inicio', $today)->count();
        
        // "Pendientes" (programada o confirmada)
        $citasPendientes = Cita::whereIn('estado', ['programada', 'confirmada'])->count();
        
        $citasCompletadas = Cita::where('estado', 'completada')->count();
        $citasCanceladas = Cita::where('estado', 'cancelada')->count();
        $totalCitas = Cita::count(); // Total histórico
        $citasEsteMes = Cita::where('fecha_hora_inicio', '>=', $startOfMonth)->count();

        // --- 4. Cálculo de Tasas ---
        $totalCitasGestionadas = $citasCompletadas + $citasCanceladas;
        
        $tasaCompletacion = ($totalCitasGestionadas > 0) 
            ? round(($citasCompletadas / $totalCitasGestionadas) * 100, 2) 
            : 0;
            
        $tasaCancelacion = ($totalCitasGestionadas > 0) 
            ? round(($citasCanceladas / $totalCitasGestionadas) * 100, 2) 
            : 0;

        // --- 5. Citas Recientes (Para la tabla) ---
        $citasRecientes = Cita::with(['paciente.user', 'medico.user'])
            ->orderBy('fecha_hora_inicio', 'desc')
            ->limit(5) // Obtenemos las 5 más recientes
            ->get();

        // --- 6. Ensamblar Respuesta ---
        return response()->json([
            // Tarjetas Superiores
            'totalPacientes' => $totalPacientes,
            'totalMedicos' => $totalMedicos,
            'citasHoy' => $citasHoy,
            'citasPendientes' => $citasPendientes, // Para la tarjeta "Pendientes"

            // Sección "Actividad del Sistema"
            'citasCompletadas' => $citasCompletadas,
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