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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellidos');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password'); // Laravel hashea esto automáticamente
        $table->enum('rol', ['paciente', 'medico', 'admin']);
        $table->boolean('activo')->default(true);
        $table->rememberToken();
        $table->timestamps(); // fecha_creacion y fecha_actualizacion
    });
}
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
