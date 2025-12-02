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

class CitaController extends Controller
{
    /**
     * Muestra una lista de las citas, filtrada por rol, estado y FECHA.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cita::class);

        $user = Auth::user();
        
        // Cargamos relaciones (incluyendo especialidad para el frontend)
        $query = Cita::with(['paciente.user', 'medico.user', 'medico.especialidad']);

        // --- 1. Filtro por ROL ---
        if ($user->hasRole('paciente')) {
            $query->where('paciente_id', $user->paciente->id);
        } elseif ($user->hasRole('medico')) {
            $query->where('medico_id', $user->medico->id);
        }

        // --- 2. NUEVO: Filtro por FECHA (Agenda de Hoy) ---
        // El frontend envía: /api/citas?fecha=2025-11-28
        if ($request->has('fecha')) {
            $query->whereDate('fecha_hora_inicio', $request->get('fecha'));
        }

        // --- 3. Filtro por ESTADO ---
        if ($request->has('estado')) {
            $estado = $request->get('estado');
            
            if ($estado === 'pendientes') {
                $query->where('estado', 'programada');
            } elseif ($estado === 'activas') {
                $query->whereIn('estado', ['programada', 'confirmada']);
            } elseif (in_array($estado, ['completada', 'cancelada'])) {
                $query->where('estado', $estado);
            }
        }

        // --- RESPUESTA ---
        // Si nos piden una fecha específica (Agenda), devolvemos todo sin paginar ordenado por hora
        if ($request->has('fecha')) {
             return CitaResource::collection($query->orderBy('fecha_hora_inicio', 'asc')->get());
        }

        // Si es listado general, paginamos
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