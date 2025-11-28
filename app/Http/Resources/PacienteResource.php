<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PacienteResource extends JsonResource
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
            'user_id' => $this->user_id,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'tipo_sangre' => $this->tipo_sangre,
            'alergias' => $this->alergias,
            // Agrega cualquier otro campo de la tabla 'pacientes' que quieras devolver
        ];
    }
}