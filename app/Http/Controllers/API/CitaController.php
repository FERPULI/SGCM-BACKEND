<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // <--- 1. IMPORTANTE: Agregamos Gate
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Muestra una lista de las citas, filtrada por rol.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cita::class);

        $user = Auth::user();
        $query = Cita::with(['paciente.user', 'medico.user', 'medico.especialidad']);

        // --- 1. Filtro por ROL (Seguridad Base) ---
        if ($user->hasRole('paciente')) {
            $query->where('paciente_id', $user->paciente->id);
        } elseif ($user->hasRole('medico')) {
            $query->where('medico_id', $user->medico->id);
        }

        // --- 2. Filtro por ESTADO (Para las pestañas del Frontend) ---
        // Frontend enviará: ?estado=pendientes o ?estado=completadas
        if ($request->has('estado')) {
            $estado = $request->get('estado');
            
            if ($estado === 'pendientes') {
                // Asumimos que 'pendientes' son las programadas
                $query->where('estado', 'programada');
            } 
            elseif ($estado === 'activas') {
                // Activas podrían ser programadas Y confirmadas
                $query->whereIn('estado', ['programada', 'confirmada']);
            }
            elseif (in_array($estado, ['completada', 'cancelada'])) {
                $query->where('estado', $estado);
            }
        }

        // Ordenar: Las más recientes (o futuras) primero
        $citas = $query->orderBy('fecha_hora_inicio', 'desc')->paginate(10);
        
        return CitaResource::collection($citas);
    }
    /**
     * Almacena una nueva cita.
     */
    public function store(StoreCitaRequest $request)
    {
        // 1. Autorizar
        Gate::authorize('create', Cita::class);

        $datosValidados = $request->validated();
        $user = Auth::user();

        // 2. FORZAR el ID del paciente si el que crea es un paciente
        if ($user->hasRole('paciente')) {
            $datosValidados['paciente_id'] = $user->paciente->id;
        }
        
        // Si no se envió la hora fin, la calculamos (30 min por defecto)
        if (!isset($datosValidados['fecha_hora_fin'])) {
             $inicio = \Carbon\Carbon::parse($datosValidados['fecha_hora_inicio']);
             $datosValidados['fecha_hora_fin'] = $inicio->addMinutes(30)->format('Y-m-d H:i:s');
        }

        $datosValidados['estado'] = 'programada';
        
        $cita = Cita::create($datosValidados);
        $cita->load(['paciente.user', 'medico.user']);

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
        
        $cita->load(['paciente.user', 'medico.user']);
        return new CitaResource($cita);
    }

    /**
     * Actualiza una cita.
     */
    public function update(UpdateCitaRequest $request, Cita $cita)
    {
        Gate::authorize('update', $cita);

        // Si la cita ya pasó o está cancelada, no se puede editar
        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json(['message' => 'No se puede editar una cita finalizada o cancelada.'], 409);
        }

        $datos = $request->validated();
        
        // Si el usuario está reprogramando (cambiando fecha), reseteamos estado a programada
        if (isset($datos['fecha_hora_inicio'])) {
             $datos['estado'] = 'programada';
        }

        $cita->update($datos);
        $cita->load(['paciente.user', 'medico.user']);
        
        return new CitaResource($cita);
    }

    /**
     * Cancela una cita.
     */
    public function destroy(Cita $cita)
    {
        Gate::authorize('delete', $cita);
        
        if ($cita->estado == 'completada') {
             return response()->json([
                'message' => 'No se puede cancelar una cita que ya ha finalizado.'
            ], 409);
        }

        $cita->update(['estado' => 'cancelada']);
        $cita->load(['paciente.user', 'medico.user']);
        
        return new CitaResource($cita);
    }
}