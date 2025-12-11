<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Cita;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparamos los datos antes de validar.
     * Aquí inyectamos el ID del paciente si el usuario es Rol Paciente.
     */
    protected function prepareForValidation()
    {
        $user = $this->user();

        // 1. Si el usuario es paciente, forzamos su ID en la solicitud
        if ($user && $user->hasRole('paciente')) {
            // Aseguramos que tenga perfil de paciente
            if ($user->paciente) {
                $this->merge([
                    'paciente_id' => $user->paciente->id
                ]);
            }
        }

        // 2. Calcular fecha fin automática si no viene
        if ($this->has('fecha_hora_inicio') && !$this->has('fecha_hora_fin')) {
            $inicio = \Carbon\Carbon::parse($this->fecha_hora_inicio);
            $this->merge([
                'fecha_hora_fin' => $inicio->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id', // Ahora sí pasará porque lo inyectamos arriba
            'medico_id' => 'required|exists:medicos,id',
            'fecha_hora_inicio' => [
                'required',
                'date',
                'after:now',
                // Validación personalizada de disponibilidad (opcional si usas la Rule class)
                // new ValidarDisponibilidadMedico($this->medico_id) 
            ],
            'motivo_consulta' => 'required|string|max:255',
        ];
    }
}