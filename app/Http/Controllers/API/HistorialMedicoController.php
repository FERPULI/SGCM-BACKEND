<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistorialMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistorialMedicoController extends Controller
{
    /**
     * Muestra el historial médico del paciente autenticado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Si es paciente, solo ve SU historial
        if ($user->hasRole('paciente')) {
            $historial = HistorialMedico::whereHas('cita', function ($q) use ($user) {
                $q->where('paciente_id', $user->paciente->id);
            })
            ->with(['cita.medico.user', 'cita.medico.especialidad']) // Cargamos datos del médico y cita
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            return response()->json($historial);
        }

        return response()->json(['message' => 'Acción no permitida'], 403);
    }

    /**
     * Guarda un diagnóstico y finaliza la cita.
     * (Solo Médicos)
     */
    public function store(Request $request)
    {
        $request->validate([
            'cita_id' => 'required|exists:citas,id',
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'notas_medicas' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        // Verificar que sea médico
        if (!$user->hasRole('medico')) {
            return response()->json(['message' => 'Solo los médicos pueden crear historiales.'], 403);
        }

        // Verificar que la cita pertenezca a este médico
        $cita = Cita::findOrFail($request->cita_id);
        if ($cita->medico_id !== $user->medico->id) {
            return response()->json(['message' => 'No puedes atender la cita de otro médico.'], 403);
        }

        // Verificar que la cita no esté ya completada o cancelada
        if ($cita->estado === 'completada' || $cita->estado === 'cancelada') {
            return response()->json(['message' => 'Esta cita ya ha sido finalizada.'], 409);
        }

        try {
            DB::transaction(function () use ($request, $cita) {
                // 1. Crear el Historial
                HistorialMedico::create([
                    'cita_id' => $request->cita_id,
                    'diagnostico' => $request->diagnostico,
                    'tratamiento' => $request->tratamiento,
                    'notas_medicas' => $request->notas_medicas,
                ]);

                // 2. Actualizar estado de la Cita a COMPLETADA
                $cita->update(['estado' => 'completada']);
            });

            return response()->json(['message' => 'Consulta finalizada y historial guardado.'], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al guardar historial', 'error' => $e->getMessage()], 500);
        }
    }
}