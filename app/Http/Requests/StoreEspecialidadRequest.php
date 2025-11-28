<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspecialidadRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Asumimos que la autorización se maneja por middleware
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // El nombre es requerido, debe ser único en la tabla 'especialidades'
            'nombre' => 'required|string|max:255|unique:especialidades,nombre',
            'descripcion' => 'sometimes|string|nullable',
        ];
    }
}