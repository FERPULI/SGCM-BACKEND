<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La Policy se encargará de esto si es necesario
    }

    /**
     * Obtiene el ID del usuario de la ruta.
     */
    protected function getUserId()
    {
        return $this->route('id'); // Asumiendo que tu ruta es 'users/{id}'
    }

    public function rules(): array
    {
        $userId = $this->getUserId();

        return [
            // --- Campos de User ---
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // Ignora el ID del usuario actual
            ],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'activo' => 'sometimes|boolean',
            // No permitimos cambiar el ROL en una actualización.
            
            // --- Campos de Paciente (Opcionales) ---
            'fecha_nacimiento' => 'sometimes|nullable|date',
            'telefono' => 'sometimes|nullable|string|max:20',
            'direccion' => 'sometimes|nullable|string|max:255',
            'tipo_sangre' => 'sometimes|nullable|string|max:5',
            'alergias' => 'sometimes|nullable|string',

            // --- Campos de Médico (Opcionales) ---
            'especialidad_id' => 'sometimes|nullable|integer|exists:especialidades,id',
            'licencia_medica' => 'sometimes|nullable|string|max:50',
            'telefono_consultorio' => 'sometimes|nullable|string|max:20',
            'biografia' => 'sometimes|nullable|string',
        ];
    }
}