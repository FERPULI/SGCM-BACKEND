<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// --- IMPORTS PARA EL CORREO ---
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaCitaMail;
use App\Mail\CitaConfirmadaMail; // <--- AGREGADO

class CitaController extends Controller
{
    /**
     * Muestra una lista de las citas, filtrada por rol, estado y FECHA.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cita::class);

        $user = Auth::user();
        
        $query = Cita::with(['paciente.user', 'medico.user', 'medico.especialidad']);

        // --- 1. Filtro por ROL ---
        if ($user->hasRole('paciente')) {
            $query->where('paciente_id', $user->paciente->id);
        } elseif ($user->hasRole('medico')) {
            $query->where('medico_id', $user->medico->id);
        }

        // --- 2. Filtro por FECHA (Agenda de Hoy) ---
        if ($request->has('fecha')) {
            $query->whereDate('fecha_hora_inicio', $request->get('fecha'));
        }

        // --- 3. Filtro por ESTADO ---
        if ($request->has('estado')) {
            $estado = $request->get('estado');
            
            switch ($estado) {
                case 'pendientes':
                    $query->where('estado', 'programada');
                    break;
                case 'confirmadas':
                    $query->where('estado', 'confirmada');
                    break;
                case 'completadas':
                    $query->where('estado', 'completada');
                    break;
                case 'canceladas':
                    $query->where('estado', 'cancelada');
                    break;
                case 'todas':
                    break;
                default:
                    $query->where('estado', $estado);
                    break;
            }
        }

        // --- RESPUESTA ---
        if ($request->has('fecha')) {
             return CitaResource::collection($query->orderBy('fecha_hora_inicio', 'asc')->get());
        }

        $citas = $query->orderBy('fecha_hora_inicio', 'desc')->paginate(10);
        return CitaResource::collection($citas);
    }

    /**
     * Almacena una nueva cita.
     */
    public function store(StoreCitaRequest $request)
    {
        Gate::authorize('create', Cita::class);

        $datosValidados = $request->validated();
        $user = Auth::user();

        if ($user->hasRole('paciente')) {
            $datosValidados['paciente_id'] = $user->paciente->id;
        }
        
        if (!isset($datosValidados['fecha_hora_fin'])) {
             $inicio = \Carbon\Carbon::parse($datosValidados['fecha_hora_inicio']);
             $datosValidados['fecha_hora_fin'] = $inicio->addMinutes(30)->format('Y-m-d H:i:s');
        }

        $datosValidados['estado'] = 'programada';
        
        $cita = Cita::create($datosValidados);
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);

        // =========================================================
        // 📧 NOTIFICACIÓN: NUEVA CITA REGISTRADA
        // =========================================================
        try {
            // Verificamos que el paciente tenga usuario y email
            if ($cita->paciente && $cita->paciente->user) {
                Mail::to($cita->paciente->user->email)->send(new NuevaCitaMail($cita));
            }
        } catch (\Exception $e) {
           // ESTO NOS MOSTRARÁ EL ERROR EN POSTMAN
            return response()->json([
                'message' => 'La cita se guardó, PERO el correo falló.',
                'error_tecnico' => $e->getMessage()
            ], 500);}

        return (new CitaResource($cita))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra una cita específica.
     */
    public function show(Cita $cita)
    {
        Gate::authorize('view', $cita);
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);
        return new CitaResource($cita);
    }

    /**
     * Actualiza una cita.
     */
    public function update(UpdateCitaRequest $request, Cita $cita)
    {
        Gate::authorize('update', $cita);

        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json(['message' => 'No se puede editar una cita finalizada o cancelada.'], 409);
        }

        $datos = $request->validated();
        
        if (isset($datos['fecha_hora_inicio'])) {
             $datos['estado'] = 'programada';
        }

        $cita->update($datos);
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);

        // =========================================================
        // 📧 NOTIFICACIÓN: CITA CONFIRMADA (MODO DEPURACIÓN)
        // =========================================================
        if ($cita->wasChanged('estado') && $cita->estado === 'confirmada') {
            try {
                if ($cita->paciente && $cita->paciente->user) {
                    Mail::to($cita->paciente->user->email)->send(new CitaConfirmadaMail($cita));
                }
            } catch (\Exception $e) {
                // ⚠️ AQUÍ ESTÁ EL CAMBIO: Devuelve el error técnico en pantalla
                return response()->json([
                    'message' => 'La cita se actualizó, PERO el correo falló.',
                    'error_tecnico' => $e->getMessage()
                ], 500);
            }
        }
        
        return new CitaResource($cita);
    }

    /**
     * Cancela una cita.
     */
    public function destroy(Cita $cita)
    {
        Gate::authorize('delete', $cita);
        
        if ($cita->estado == 'completada') {
             return response()->json(['message' => 'No se puede cancelar una cita que ya ha finalizado.'], 409);
        }

        $cita->update(['estado' => 'cancelada']);
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);
        
        return new CitaResource($cita);
    }
}