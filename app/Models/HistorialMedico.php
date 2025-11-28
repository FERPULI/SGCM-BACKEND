<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialMedico extends Model
{
    use HasFactory;

    protected $table = 'historiales_medicos';

    protected $fillable = [
        'cita_id',
        'paciente_id', // <-- Nuevo campo del SQL
        'medico_id',   // <-- Nuevo campo del SQL
        'diagnostico',
        'tratamiento',
        'recetas',             // <-- Nuevo campo del SQL
        'notas_privadas_medico' // <-- Nuevo campo del SQL (Antes notas_medicas)
    ];

    /**
     * Relaciones
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
    
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id', 'id');
    }
}