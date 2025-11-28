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
     * (Lógica reordenada para priorizar bloqueos)
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

        // --- 1. (NUEVO) Validar Bloqueos PRIMERO (Tabla: bloqueos_horario) ---
        $conflictoBloqueo = BloqueoHorario::where('medico_id', $medicoId)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                 // Comprueba cualquier solapamiento
                 $query->where('fecha_hora_inicio', '<', $fechaFin)
                       ->where('fecha_hora_fin', '>', $fechaInicio);
            })
            ->exists();

        if ($conflictoBloqueo) {
            // ¡Este es el error que deberías haber visto!
            $fail('El médico ha bloqueado este horario por motivos personales o emergencia.');
            return;
        }

        // --- 2. Validar Horario Laboral (Tabla: disponibilidad_medicos) ---
        $diaSemana = $fechaInicio->dayOfWeek; // 0=Domingo, 1=Lunes...
        $horaInicio = $fechaInicio->format('H:i:s');
        
        $disponibilidad = DisponibilidadMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            // Comprobamos si la hora de inicio de la cita está DENTRO de algún turno
            ->where('hora_inicio', '<=', $horaInicio) 
            // Comprobamos si la hora de fin de la cita está DENTRO de ese mismo turno
            ->where('hora_fin', '>=', $fechaFin->format('H:i:s')) 
            ->first();

        if (!$disponibilidad) {
            // Este es el error que SÍ recibiste
            $fail('El médico no está disponible en este día u horario.');
            return;
        }

        // --- 3. Validar Conflictos de Citas (Tabla: citas) ---
        $conflictoCita = Cita::where('medico_id', $medicoId)
            ->where('estado', '!=', 'cancelada') 
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                // Lógica de solapamiento
                $query->where('fecha_hora_inicio', '<', $fechaFin)
                      ->where('fecha_hora_fin', '>', $fechaInicio);
            })
            ->exists();

        if ($conflictoCita) {
            $fail('El médico ya tiene una cita programada en este horario.');
            return;
        }

        // Si pasa las 3 comprobaciones, la validación es exitosa.
    }
}