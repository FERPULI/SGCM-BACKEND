<?php

namespace App\Models; // <-- ¡ESTA ES LA CORRECCIÓN!

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisponibilidadMedico extends Model
{
    use HasFactory;
    protected $table = 'disponibilidad_medicos';
    protected $fillable = [
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];
}