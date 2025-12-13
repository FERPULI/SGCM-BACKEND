<?php

namespace App\Rules;

use App\Models\BloqueoHorario;
use App\Models\Cita;
use App\Models\DisponibilidadMedico;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarDisponibilidadMedico implements ValidationRule, DataAwareRule
{
    /**
     * Todos los datos de la solicitud.
     */
    protected $data = [];

    /**
     * Establece los datos de la solicitud.
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Ejecuta la regla de validación.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // --- Obtenemos todos los datos necesarios ---
        $fechaInicio = Carbon::parse($value); 
        $fechaFin = Carbon::parse($this->data['fecha_hora_fin'] ?? $fechaInicio->copy()->addMinutes(30));
        $medicoId = $this->data['medico_id'] ?? null;
        
        if (!$medicoId) {
            return; // Otra regla se encargará
        }

        // --- 1. Validar Bloqueos ---
        $conflictoBloqueo = BloqueoHorario::where('medico_id', $medicoId)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_hora_inicio', '<', $fechaFin)
                      ->where('fecha_hora_fin', '>', $fechaInicio);
            })
            ->exists();

        if ($conflictoBloqueo) {
            $fail('El médico ha bloqueado este horario por motivos personales o emergencia.');
            return;
        }

        // --- 2. Validar Horario Laboral ---
        $diaSemana = $fechaInicio->dayOfWeek;
        $horaInicio = $fechaInicio->format('H:i:s');
        
        $disponibilidad = DisponibilidadMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $horaInicio)
            ->where('hora_fin', '>=', $fechaFin->format('H:i:s'))
            ->first();

        if (!$disponibilidad) {
            $fail('El médico no está disponible en este día u horario.');
            return;
        }

        // --- 3. Validar Conflictos con otras Citas ---
        $conflictoCita = Cita::where('medico_id', $medicoId)
            ->where('estado', '!=', 'cancelada') 
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_hora_inicio', '<', $fechaFin)
                      ->where('fecha_hora_fin', '>', $fechaInicio);
            })
            ->exists();

        if ($conflictoCita) {
            $fail('El médico ya tiene una cita programada en este horario.');
            return;
        }
    }

    /**
     * Método añadido solo para compatibilidad con pruebas unitarias
     * No interfiere con el sistema moderno de validación.
     */
    public function message(): string
    {
        return 'El médico no está disponible en este horario.';
    }
}
