<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    /**
     * Los atributos que se pueden asignar en masa.
     * (Corregidos para coincidir con tu SQL)
     */
    protected $fillable = [
        'paciente_id',
        'medico_id',
        'fecha_hora_inicio', // <-- CORREGIDO
        'fecha_hora_fin',    // <-- CORREGIDO
        'estado',
        'motivo_consulta',
        'notas_paciente',    // <-- CORREGIDO
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'fecha_hora_inicio' => 'datetime', // <-- CORREGIDO
        'fecha_hora_fin'    => 'datetime', // <-- CORREGIDO
    ];

    /**
     * Valores permitidos para el estado (según tu ENUM)
     */
    public const ESTADOS = [
        'programada', // <-- Corresponde a tu SQL
        'confirmada', 
        'cancelada', 
        'completada',
    ];

    // --- RELACIONES ---

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}