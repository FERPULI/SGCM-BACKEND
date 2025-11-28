<?php

namespace App\Http\Requests;

use App\Models\Cita;
use App\Rules\ValidarDisponibilidadMedico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Importante para verificar el rol

class UpdateCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización detallada se maneja en la Policy/Controller
    }

    public function rules(): array
    {
        // Obtenemos la cita que se está intentando actualizar
        // (Laravel inyecta el modelo automáticamente si usas Route Model Binding)
        $cita = $this->route('cita'); 

        return [
            // --- REPROGRAMACIÓN (Cambio de Fecha) ---
            // Si envían una nueva fecha, DEBE validarse la disponibilidad del médico
            'fecha_hora_inicio' => [
                'sometimes',
                'date',
                'after_or_equal:today',
                // Esta regla es la clave: Verifica que el médico esté libre en la NUEVA fecha
                new ValidarDisponibilidadMedico() 
            ],
            
            // Si cambian la fecha de inicio, calculamos o validamos la de fin
            'fecha_hora_fin' => 'sometimes|date|after:fecha_hora_inicio',
            
            // --- CAMBIO DE ESTADO ---
            'estado' => ['sometimes', Rule::in(Cita::ESTADOS)],
            
            // --- NOTAS (Médicos) ---
            'notas_paciente' => 'sometimes|string|nullable|max:1000', // (Tu BD usa notas_paciente)
            
            // --- OTROS ---
            'motivo_consulta' => 'sometimes|string|max:255',
            
            // IMPORTANTE: Para que ValidarDisponibilidadMedico funcione, 
            // necesitamos el medico_id. Si no viene en el request (porque no se cambia),
            // debemos inyectarlo desde la cita existente para que la regla lo lea.
            // (Esto se hace en prepareForValidation abajo, pero aquí validamos si lo envían)
            'medico_id' => 'sometimes|exists:medicos,id',
        ];
    }

    /**
     * Preparar los datos antes de validar.
     */
    protected function prepareForValidation()
    {
        // 1. Inyectar medico_id si falta (necesario para la regla de disponibilidad)
        if ($this->has('fecha_hora_inicio') && !$this->has('medico_id')) {
            // Obtenemos la cita actual de la ruta
            $cita = $this->route('cita');
            if ($cita) {
                $this->merge(['medico_id' => $cita->medico_id]);
            }
        }

        // 2. Calcular fecha fin automática si reprograman
        if ($this->has('fecha_hora_inicio') && !$this->has('fecha_hora_fin')) {
            $inicio = \Carbon\Carbon::parse($this->fecha_hora_inicio);
            $this->merge([
                'fecha_hora_fin' => $inicio->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            ]);
        }
    }
}