<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Almacena notificaciones para usuarios (recordatorios, cancelaciones).
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // El usuario que recibe la notificacion
            
            $table->text('mensaje');
            $table->enum('tipo', ['recordatorio_cita', 'cita_cancelada', 'cita_reprogramada', 'nuevo_historial', 'admin_mensaje']);
            $table->boolean('leida')->default(false);
            
            $table->timestamps();
            
            $table->index(['usuario_id', 'leida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
