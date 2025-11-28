<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'especialidades';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = true; // Tu SQL las incluye

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // --- RELACIONES ---

    /**
     * Define la relación de "uno a muchos" con los Médicos.
     * Una especialidad puede tener muchos médicos.
     */
    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class, 'especialidad_id');
    }
}