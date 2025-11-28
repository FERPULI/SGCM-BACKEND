<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PacienteDashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = Auth::user();

        // Seguridad: Asegurarse de que sea un paciente
        if (!$user->paciente) {
            return response()->json(['message' => 'Perfil de paciente no encontrado'], 403);
        }
        
        $pacienteId = $user->paciente->id;
        $now = Carbon::now();

        // 1. Contador: Citas Programadas (Futuras y activas)
        $citasProgramadasCount = Cita::where('paciente_id', $pacienteId)
            ->whereIn('estado', ['programada', 'confirmada'])
            ->where('fecha_hora_inicio', '>=', $now)
            ->count();

        // 2. Contador: Historial (Completadas)
        $historialCount = Cita::where('paciente_id', $pacienteId)
            ->where('estado', 'completada')
            ->count();

        // 3. Próxima Cita (La tarjeta grande del dashboard)
        $proximaCita = Cita::with(['medico.user', 'medico.especialidad'])
            ->where('paciente_id', $pacienteId)
            ->whereIn('estado', ['programada', 'confirmada'])
            ->where('fecha_hora_inicio', '>=', $now)
            ->orderBy('fecha_hora_inicio', 'asc')
            ->first();

        return response()->json([
            'resumen' => [
                'citas_programadas' => $citasProgramadasCount,
                'historial_completado' => $historialCount,
            ],
            'proxima_cita' => $proximaCita ? new CitaResource($proximaCita) : null
        ]);
    }
}