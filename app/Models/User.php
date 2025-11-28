<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne; // <-- Importante

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password',
        'rol',
        'activo',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean', // Tu modelo original lo tenía
    ];

    // Helper: comprobar rol
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->rol, $roles);
        }
        return $this->rol === $roles;
    }

    // Nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    // --- RELACIONES DE PERFIL (AÑADIDAS) ---

    /**
     * Define la relación "uno a uno" con el perfil de Paciente.
     */
    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class, 'usuario_id');
    }

    /**
     * Define la relación "uno a uno" con el perfil de Médico.
     */
    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'usuario_id');
    }
}