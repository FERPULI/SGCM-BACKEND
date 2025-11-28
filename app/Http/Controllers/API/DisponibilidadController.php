<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BloqueoHorario;
use App\Models\Cita;
use App\Models\DisponibilidadMedico;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    /**
     * Devuelve los horarios disponibles (slots) para un médico en una fecha específica.
     */
    public function getSlots(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'fecha' => 'required|date|after_or_equal:today',
        ]);

        $medicoId = $request->medico_id;
        $fecha = Carbon::parse($request->fecha);
        $diaSemana = $fecha->dayOfWeek; // 0=Domingo, 1=Lunes...

        // 1. Obtener el horario base del médico para ese día de la semana
        $horariosBase = DisponibilidadMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->get();

        if ($horariosBase->isEmpty()) {
            return response()->json(['slots' => []]); // No trabaja ese día
        }

        // 2. Generar todos los slots posibles de 30 minutos
        $todosLosSlots = [];
        $duracionCita = 30; // Minutos

        foreach ($horariosBase as $horario) {
            $inicioTurno = Carbon::parse($request->fecha . ' ' . $horario->hora_inicio);
            $finTurno = Carbon::parse($request->fecha . ' ' . $horario->hora_fin);

            // Generar periodos de 30 min
            $periodo = CarbonPeriod::create($inicioTurno, $duracionCita . ' minutes', $finTurno);

            foreach ($periodo as $hora) {
                // Evitamos agregar el slot que coincide exactamente con la hora de fin del turno
                if ($hora->format('H:i') < $finTurno->format('H:i')) {
                    $todosLosSlots[] = $hora->format('H:i:s');
                }
            }
        }

        // 3. Obtener Citas existentes para ese día
        $citasOcupadas = Cita::where('medico_id', $medicoId)
            ->whereDate('fecha_hora_inicio', $fecha)
            ->where('estado', '!=', 'cancelada')
            ->pluck('fecha_hora_inicio') // Obtenemos solo la hora de inicio (ej: "2025-11-27 10:00:00")
            ->map(function ($fechaHora) {
                return Carbon::parse($fechaHora)->format('H:i:s');
            })
            ->toArray();

        // 4. Obtener Bloqueos para ese día
        $bloqueos = BloqueoHorario::where('medico_id', $medicoId)
            ->where(function($q) use ($fecha) {
                $q->whereDate('fecha_hora_inicio', $fecha)
                  ->orWhereDate('fecha_hora_fin', $fecha);
            })
            ->get();

        // 5. FILTRADO FINAL
        $slotsDisponibles = [];

        foreach ($todosLosSlots as $slot) {
            // Convertimos el slot actual a Carbon completo para comparar con bloqueos
            $slotInicio = Carbon::parse($request->fecha . ' ' . $slot);
            $slotFin = $slotInicio->copy()->addMinutes($duracionCita);

            // A. Verificar si ya hay una cita exacta a esta hora
            if (in_array($slot, $citasOcupadas)) {
                continue; // Está ocupado por una cita
            }

            // B. Verificar Bloqueos (solapamiento)
            $esBloqueado = false;
            foreach ($bloqueos as $bloqueo) {
                // Si el slot se solapa con el bloqueo
                if ($slotInicio < $bloqueo->fecha_hora_fin && $slotFin > $bloqueo->fecha_hora_inicio) {
                    $esBloqueado = true;
                    break;
                }
            }

            if (!$esBloqueado) {
                // Formateamos para que el frontend lo vea bonito (ej: "09:00")
                $slotsDisponibles[] = substr($slot, 0, 5); 
            }
        }

        return response()->json([
            'fecha' => $request->fecha,
            'slots' => $slotsDisponibles
        ]);
    }
}