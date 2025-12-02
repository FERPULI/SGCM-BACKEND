<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_fin' => $this->fecha_hora_fin,
            'estado' => $this->estado,
            'motivo_consulta' => $this->motivo_consulta,
            'notas_paciente' => $this->notas_paciente,
            
            'paciente' => [
                'id' => $this->paciente?->id,
                'nombre_completo' => $this->paciente?->user?->nombre_completo ?? 'N/A',
                'email' => $this->paciente?->user?->email ?? 'N/A',
                // --- DATOS NUEVOS PARA EL MÉDICO ---
                'telefono' => $this->paciente?->telefono,
                'tipo_sangre' => $this->paciente?->tipo_sangre ?? 'N/A',
                'alergias' => $this->paciente?->alergias ?? 'Ninguna',
                'fecha_nacimiento' => $this->paciente?->fecha_nacimiento,
                // Calculamos la edad automáticamente
                'edad' => $this->paciente?->fecha_nacimiento 
                    ? Carbon::parse($this->paciente->fecha_nacimiento)->age 
                    : 'N/A',
            ],
            
            'medico' => [
                'id' => $this->medico?->id,
                'nombre_completo' => $this->medico?->user?->nombre_completo ?? 'N/A',
                'especialidad' => [
                    'id' => $this->medico?->especialidad?->id,
                    'nombre' => $this->medico?->especialidad?->nombre,
                ]
            ],
        ];
    }
}