<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario_id',
        'fecha_nacimiento',
        'telefono',
        'direccion',
        'genero',
        'tipo_sangre',
        'alergias',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // ───────── RELACIONES ─────────

    /**
     * Un perfil de paciente pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Un paciente puede tener muchas Citas.
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    /**
     * Un paciente puede tener muchos registros de Historial Médico.
     */
    public function historialesMedicos(): HasMany
    {
        return $this->hasMany(HistorialMedico::class, 'paciente_id');
    }
}
