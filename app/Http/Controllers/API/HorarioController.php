<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DisponibilidadMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    /**
     * Obtener los horarios actuales del médico.
     */
    public function index(Request $request)
    {
        $medico = $request->user()->medico;
        
        // Devuelve array: [{ dia_semana: 1, hora_inicio: '09:00', ... }]
        $horarios = DisponibilidadMedico::where('medico_id', $medico->id)
            ->orderBy('dia_semana')
            ->get();

        return response()->json($horarios);
    }

    /**
     * Guardar configuración de horarios (Sobrescribe lo anterior).
     * El frontend debe enviar la lista completa de horarios activos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'horarios' => 'array', // Lista de días trabajados
            'horarios.*.dia_semana' => 'required|integer|between:0,6', // 0=Dom, 1=Lun
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i|after:horarios.*.hora_inicio',
        ]);

        $medico = $request->user()->medico;

        // Estrategia simple: Borrar todo lo anterior e insertar lo nuevo
        // Esto facilita la edición en el frontend.
        DisponibilidadMedico::where('medico_id', $medico->id)->delete();

        foreach ($request->horarios as $horario) {
            DisponibilidadMedico::create([
                'medico_id' => $medico->id,
                'dia_semana' => $horario['dia_semana'],
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fin' => $horario['hora_fin'],
            ]);
        }

        return response()->json(['message' => 'Horario actualizado correctamente.']);
    }
}