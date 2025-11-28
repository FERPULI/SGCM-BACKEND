<?php

namespace App\Models; // <-- ¡ESTA ES LA CORRECCIÓN!

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueoHorario extends Model
{
    use HasFactory;
    protected $table = 'bloqueos_horario';
    protected $fillable = [
        'medico_id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'motivo',
    ];
}