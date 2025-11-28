<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password; // <-- Importante

class StoreUserRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Asumimos que la autorización se maneja en otro lugar (ej. middleware)
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Reglas de Users (basadas en tu SQL y modelo User)
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()], // 'confirmed' valida password_confirmation
            'rol' => 'required|in:paciente,medico,admin', // Basado en tu SQL
            'activo' => 'sometimes|boolean',

            // --- Reglas Condicionales para Paciente (tabla 'pacientes') ---
            'fecha_nacimiento' => 'required_if:rol,paciente|date|nullable',
            'telefono' => 'required_if:rol,paciente|string|max:255|nullable',
            'direccion' => 'sometimes|string|nullable',
            'tipo_sangre' => 'sometimes|string|max:5|nullable',
            'alergias' => 'sometimes|string|nullable',

            // --- Reglas Condicionales para Médico (tabla 'medicos') ---
            // 'exists' valida que la especialidad_id exista en la tabla 'especialidades'
            'especialidad_id' => 'required_if:rol,medico|integer|exists:especialidades,id',
            'licencia_medica' => 'required_if:rol,medico|string|max:255|unique:medicos,licencia_medica|nullable',
            'telefono_consultorio' => 'sometimes|string|max:255|nullable',
            'biografia' => 'sometimes|string|nullable',
        ];
    }
}