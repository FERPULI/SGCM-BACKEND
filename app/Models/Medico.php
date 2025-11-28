<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Importante

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';

    protected $fillable = [
        'user_id',
        'especialidad_id',
        'licencia_medica',
        'telefono_consultorio',
        'biografia',
    ];

    /**
     * Un perfil de médico pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Un médico pertenece a una Especialidad.
     */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    /**
     * Un médico tiene muchas Citas.
     * (ESTA ES LA FUNCIÓN QUE FALTABA)
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }
}