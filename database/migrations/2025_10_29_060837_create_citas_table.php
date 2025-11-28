<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('citas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained('pacientes');
        $table->foreignId('medico_id')->constrained('medicos');
        $table->dateTime('fecha_hora_inicio');
        $table->dateTime('fecha_hora_fin');
        $table->enum('estado', ['programada', 'confirmada', 'cancelada', 'completada'])->default('programada');
        $table->text('motivo_consulta')->nullable();
        $table->text('notas_paciente')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
