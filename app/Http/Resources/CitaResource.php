<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // --- DATOS ORIGINALES ---
            'id' => $this->id,
            'fecha_hora_inicio' => $this->fecha_hora_inicio,
            'fecha_hora_fin' => $this->fecha_hora_fin,
            'estado' => $this->estado,
            'motivo_consulta' => $this->motivo_consulta,
            'notas_paciente' => $this->notas_paciente,
            
            // --- RELACIONES ORIGINALES ---
            'paciente' => [
                'id' => $this->paciente?->id,
                'nombre_completo' => $this->paciente?->user?->nombre_completo ?? 'N/A',
            ],
            
            'medico' => [
                'id' => $this->medico?->id,
                'nombre_completo' => $this->medico?->user?->nombre_completo ?? 'N/A',
                
                // --- NUEVO: Especialidad ---
                // Agregamos esto para que el dashboard pueda mostrar "Cardiología"
                'especialidad' => [
                    'id' => $this->medico?->especialidad?->id ?? null,
                    'nombre' => $this->medico?->especialidad?->nombre ?? 'Sin asignar',
                ]
            ],
        ];
    }
}