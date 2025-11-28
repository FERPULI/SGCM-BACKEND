<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicoResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['user', 'especialidad']);

        return [
            // --- Datos del Perfil (para la tarjeta) ---
            'id_medico' => $this->id,
            
            // --- CORRECCIÓN: Usamos ?-> (operador null-safe) ---
            // Esto evita el error si $this->user es null
            'id_usuario' => $this->user?->id,
            'nombre_completo' => $this->user?->nombre_completo ?? 'Usuario No Encontrado',
            'email' => $this->user?->email ?? 'N/A',
            
            // --- Datos del Médico ---
            'licencia_medica' => $this->licencia_medica,
            'telefono_consultorio' => $this->telefono_consultorio,
            'biografia' => $this->biografia,
            
            // --- Datos de la Especialidad (Ya era defensivo) ---
            'especialidad' => [
                'id' => $this->especialidad?->id, // <-- También aplicamos ?-> aquí por si acaso
                'nombre' => $this->especialidad?->nombre ?? 'Sin especialidad',
            ],

            // --- Estadísticas ---
            'estadisticas' => [
                'citas_totales' => $this->citas_count ?? 0,
                'citas_completadas' => $this->citas_completadas_count ?? 0,
                'citas_pendientes' => $this->citas_pendientes_count ?? 0,
                'pacientes_atendidos' => $this->pacientes_atendidos_count ?? 0,
            ]
        ];
    }
}