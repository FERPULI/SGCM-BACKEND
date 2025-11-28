<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha_hora_inicio' => $this->fecha_hora_inicio, // <-- Corresponde a tu SQL
            'fecha_hora_fin' => $this->fecha_hora_fin,       // <-- Corresponde a tu SQL
            'estado' => $this->estado,
            'motivo_consulta' => $this->motivo_consulta,
            'notas_paciente' => $this->notas_paciente,     // <-- Corresponde a tu SQL
            
            'paciente' => [
                'id' => $this->whenLoaded('paciente', $this->paciente->id),
                'nombre_completo' => $this->whenLoaded('paciente', $this->paciente->user->nombre_completo ?? null),
            ],
            'medico' => [
                'id' => $this->whenLoaded('medico', $this->medico->id),
                'nombre_completo' => $this->whenLoaded('medico', $this->medico->user->nombre_completo ?? null),
            ],
        ];
    }
}