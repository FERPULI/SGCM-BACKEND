<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EspecialidadController;
use App\Http\Controllers\API\CitaController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\MedicoController;
use App\Http\Controllers\API\PacienteDashboardController;
use App\Http\Controllers\API\DisponibilidadController;
use App\Http\Controllers\API\HistorialMedicoController;
use App\Http\Controllers\API\HorarioController;
use App\Http\Controllers\API\CitaController; 

Route::post('auth/login', [AuthController::class,'login']);
Route::post('auth/register', [AuthController::class,'register']);

// =========================================================================
// RUTA PÚBLICA DE CITAS (SOLUCIÓN TEMPORAL)
// =========================================================================
Route::get('appointments', [CitaController::class, 'index']);
Route::post('appointments', [CitaController::class, 'store']);
Route::put('appointments/{id}', [CitaController::class, 'update']); 
// 👇 AQUÍ ESTÁ EL ERROR: Cambia 'citas' por 'appointments'
Route::delete('appointments/{id}', [CitaController::class, 'destroy']);

// 👇 MUEVE ESTAS DOS AQUÍ (Sin la barra '/' inicial y fuera del auth)
Route::get('patients-list', [CitaController::class, 'listarPacientes']);
Route::get('doctors-list', [CitaController::class, 'listarMedicos']);
// =========================================================================


Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class,'logout']);
    Route::post('auth/logout-all', [AuthController::class,'logoutAll']);
    Route::get('auth/profile', [AuthController::class,'profile']);
    Route::put('auth/profile', [AuthController::class,'updateProfile']);
    Route::get('paciente/dashboard', [PacienteDashboardController::class, 'getStats']);
    Route::get('slots-disponibles', [DisponibilidadController::class, 'getSlots']);
    
    Route::get('paciente/historial', [HistorialMedicoController::class, 'index']); 
    Route::post('medico/finalizar-consulta', [HistorialMedicoController::class, 'store']); 
    
    // La ruta de appointments también la puedes dejar aquí o subirla si falla
    Route::get('appointments', [CitaController::class, 'index']);

     // --- RUTAS DE CITAS ---
    // (Pacientes, Médicos y Admin pueden necesitar acceso)
    Route::apiResource('citas', CitaController::class);

    // CRUD usuarios (admin)
   // Route::middleware('role:admin')->group(function () {
        // --- 2. AÑADIR RUTA DE DASHBOARD ---
        Route::get('dashboard-stats', [DashboardController::class, 'getStats']);     
   
   // --- CRUD de Usuarios ---
        Route::get('users', [UserController::class,'index']);
        Route::post('users', [UserController::class,'store']);

        // --- RUTA MOVIDA AQUÍ ---
        // Puesta ANTES de 'users/{id}' para evitar conflictos
        Route::get('users/counts', [UserController::class, 'getCounts']);
        
        // Rutas de usuario con {id}
        Route::get('users/{id}', [UserController::class,'show']);
        Route::put('users/{id}', [UserController::class,'update']);
        Route::delete('users/{id}', [UserController::class,'destroy']);
        
        // --- CRUD de Especialidades ---
        Route::apiResource('especialidades', EspecialidadController::class);
     
        // --- 2. AÑADIR RUTA PARA EL DIRECTORIO DE MÉDICOS ---
        Route::get('medicos-directorio', [MedicoController::class, 'index']);
        // });

        Route::get('medico/horarios', [HorarioController::class, 'index']);
        Route::post('medico/horarios', [HorarioController::class, 'store']);
        Route::get('medico/pacientes', [MedicoController::class, 'misPacientes']);
        Route::get('medico/stats', [MedicoController::class, 'dashboardStats']);
});
