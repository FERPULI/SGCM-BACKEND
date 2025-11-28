<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EspecialidadResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // El error de tu log está en esta sección.
        // Asegúrate de que tu return se vea exactamente así:
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion, // Esta es la línea 20 o cercana
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}