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
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            // Usamos trim para asegurar que no queden espacios vacíos
            'nombre_completo' => trim($this->nombre . ' ' . $this->apellidos),
            'email' => $this->email,
            'rol' => $this->rol,
            'activo' => (bool) $this->activo,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),

            // --- RELACIONES (CRUCIAL PARA TU APP) ---
            // Esto permite que el frontend reciba los datos del médico o paciente
            // cuando se cargan las relaciones.
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'medico' => new MedicoResource($this->whenLoaded('medico')),
        ];
    }
}