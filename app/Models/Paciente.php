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

    // --- RELACIONES ---

    /**
     * Define la relación inversa de "uno a uno" con el Usuario.
     * Un perfil de paciente pertenece a una cuenta de usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Define la relación de "uno a muchos" con las Citas.
     * Un paciente puede tener muchas citas.
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    /**
     * Define la relación de "uno a muchos" con el Historial Médico.
     * Un paciente puede tener muchos registros en su historial.
     */
    public function historialesMedicos(): HasMany
    {
        return $this->hasMany(HistorialMedico::class, 'paciente_id');
    }
}
