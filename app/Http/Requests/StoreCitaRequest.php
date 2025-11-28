<?php

namespace App\Http\Requests;

use App\Models\Cita;
use App\Rules\ValidarDisponibilidadMedico; // <-- 1. IMPORTA LA REGLA
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'medico_id' => 'required|integer|exists:medicos,id',
            
            // 2. USA LA REGLA EN 'fecha_hora_inicio'
            'fecha_hora_inicio' => [
                'required',
                'date',
                'after_or_equal:today',
                new ValidarDisponibilidadMedico() // <-- 3. APLICA LA REGLA
            ],
            
            'fecha_hora_fin' => 'required|date|after:fecha_hora_inicio',
            
            'motivo_consulta' => 'sometimes|string|max:1000|nullable',
            'notas_paciente' => 'sometimes|string|nullable', 
            
            'estado' => ['sometimes', Rule::in(Cita::ESTADOS)],
        ];
    }
    protected function prepareForValidation()
{
    // Si envían hora inicio pero NO hora fin, agregamos 30 mins automáticos
    if ($this->has('fecha_hora_inicio') && !$this->has('fecha_hora_fin')) {
        $inicio = \Carbon\Carbon::parse($this->fecha_hora_inicio);
        $this->merge([
            'fecha_hora_fin' => $inicio->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);
    }
}
}