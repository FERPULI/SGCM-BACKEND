<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Define el horario laboral recurrente de un médico.
     */
    public function up(): void
    {
        Schema::create('disponibilidad_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            
            // 0=Domingo, 1=Lunes, ..., 6=Sábado
            $table->unsignedTinyInteger('dia_semana')->comment('0=Domingo, 1=Lunes, ..., 6=Sábado');
            
            $table->time('hora_inicio');
            $table->time('hora_fin');
            
            $table->timestamps();

            // Asegura que un médico no tenga dos registros de disponibilidad para el mismo día
            $table->unique(['medico_id', 'dia_semana']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_medicos');
    }
};
