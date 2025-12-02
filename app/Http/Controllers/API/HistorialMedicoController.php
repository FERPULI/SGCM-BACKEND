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
        // 1. Validar los campos del mockup
        $request->validate([
            'cita_id' => 'required|exists:citas,id',
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'recetas' => 'nullable|string',              // <-- Nuevo
            'notas_privadas_medico' => 'nullable|string' // <-- Nuevo
        ]);

        $user = Auth::user();
        
        // Validaciones de seguridad (Rol y Pertenencia)
        if (!$user->hasRole('medico')) {
            return response()->json(['message' => 'Solo médicos.'], 403);
        }
        $cita = Cita::findOrFail($request->cita_id);
        if ($cita->medico_id !== $user->medico->id) {
            return response()->json(['message' => 'No es tu cita.'], 403);
        }
        if ($cita->estado === 'completada') {
            return response()->json(['message' => 'Cita ya finalizada.'], 409);
        }

        try {
            DB::transaction(function () use ($request, $cita, $user) {
                // 2. Crear Historial con TODOS los campos
                HistorialMedico::create([
                    'cita_id' => $request->cita_id,
                    'paciente_id' => $cita->paciente_id, // <-- Importante llenar esto
                    'medico_id' => $user->medico->id,    // <-- Importante llenar esto
                    'diagnostico' => $request->diagnostico,
                    'tratamiento' => $request->tratamiento,
                    'recetas' => $request->recetas,
                    'notas_privadas_medico' => $request->notas_privadas_medico,
                ]);

                // 3. Cerrar Cita
                $cita->update(['estado' => 'completada']);
            });

            return response()->json(['message' => 'Consulta guardada con éxito.'], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }}