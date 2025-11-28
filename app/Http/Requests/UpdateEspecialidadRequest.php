<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEspecialidadRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Obtenemos el ID de la especialidad desde la ruta
        $especialidadId = $this->route('especialidad')->id ?? $this->route('especialidad');

        return [
            // 'sometimes' = solo valida si está presente
            // Al actualizar, la regla 'unique' debe ignorar el registro actual
            'nombre' => 'sometimes|string|max:255|unique:especialidades,nombre,' . $especialidadId,
            'descripcion' => 'sometimes|string|nullable',
        ];
    }
}