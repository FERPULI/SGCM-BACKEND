<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CitaPolicy
{
    /**
     * Regla "todopoderosa" para el Administrador.
     * Si el usuario es 'admin', se le permite todo automáticamente.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null; // Deja que los otros métodos decidan
    }

    /**
     * Determina si el usuario puede ver la lista de citas (index).
     * (Lo manejaremos en el controlador, así que aquí permitimos a todos)
     */
    public function viewAny(User $user): bool
    {
        return true; // Permitimos el acceso, el controlador filtrará
    }

    /**
     * Determina si el usuario puede ver UNA cita específica.
     */
    public function view(User $user, Cita $cita): bool
    {
        // El Paciente puede verla SI es su cita
        if ($user->hasRole('paciente')) {
            return $user->paciente->id === $cita->paciente_id;
        }

        // El Médico puede verla SI es su cita
        if ($user->hasRole('medico')) {
            return $user->medico->id === $cita->medico_id;
        }

        return false; // Otros roles no pueden
    }

    /**
     * Determina si el usuario puede crear una cita.
     */
    public function create(User $user): bool
    {
        // Solo Pacientes (para sí mismos) y Admins (para cualquiera)
        return $user->hasRole('paciente') || $user->hasRole('admin');
    }

    /**
     * Determina si el usuario puede actualizar una cita.
     */
    public function update(User $user, Cita $cita): bool
    {
        // El Paciente puede actualizar SI es su cita (ej. para confirmar)
        if ($user->hasRole('paciente')) {
            return $user->paciente->id === $cita->paciente_id;
        }

        // El Médico puede actualizar SI es su cita (ej. para añadir notas)
        if ($user->hasRole('medico')) {
            return $user->medico->id === $cita->medico_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede "borrar" (cancelar) una cita.
     */
    public function delete(User $user, Cita $cita): bool
    {
        // Paciente y Médico pueden cancelar SUS propias citas
        return $this->update($user, $cita);
    }
}