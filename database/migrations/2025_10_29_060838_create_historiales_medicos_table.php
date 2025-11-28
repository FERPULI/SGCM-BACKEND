<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Almacena el diagnóstico, tratamiento y notas de una cita completada.
     */
    public function up(): void
    {
        Schema::create('historiales_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade')->unique(); // 1 a 1 con citas
            $table->foreignId('paciente_id')->constrained('pacientes'); // Redundante pero útil para consultas rápidas
            $table->foreignId('medico_id')->constrained('medicos'); // Redundante pero útil para consultas rápidas
            
            $table->text('diagnostico');
            $table->text('tratamiento')->nullable();
            $table->text('recetas')->nullable();
            $table->text('notas_privadas_medico')->nullable();
            
            $table->timestamps();
            
            $table->index('paciente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiales_medicos');
    }
};
