<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // --- Datos del Usuario (Tabla Users) ---
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombre_completo, // (Asumo que tienes un accesor para esto)
            'email' => $this->email,
            'rol' => $this->rol,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // --- AÑADIDO: Perfil de Paciente ---
            // Usa PacienteResource si la relación 'paciente' está cargada
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),

            // --- AÑADIDO: Perfil de Médico ---
            // Usa MedicoResource si la relación 'medico' está cargada
            'medico' => new MedicoResource($this->whenLoaded('medico')),
        ];
    }
}