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
     * Muestra una lista de las citas, filtrada por rol.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Cita::class);

        $user = Auth::user();
        
        // --- CAMBIO: Agregamos 'medico.especialidad' ---
        $query = Cita::with(['paciente.user', 'medico.user', 'medico.especialidad']);

        // --- LÓGICA ORIGINAL: Filtros de Rol ---
        if ($user->hasRole('paciente')) {
            $query->where('paciente_id', $user->paciente->id);
        } elseif ($user->hasRole('medico')) {
            $query->where('medico_id', $user->medico->id);
        }

        // --- LÓGICA ORIGINAL: Filtros de Estado ---
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

        // --- LÓGICA ORIGINAL: Forzar paciente ---
        if ($user->hasRole('paciente')) {
            $datosValidados['paciente_id'] = $user->paciente->id;
        }
        
        // --- LÓGICA ORIGINAL: Calcular hora fin ---
        if (!isset($datosValidados['fecha_hora_fin'])) {
             $inicio = \Carbon\Carbon::parse($datosValidados['fecha_hora_inicio']);
             $datosValidados['fecha_hora_fin'] = $inicio->addMinutes(30)->format('Y-m-d H:i:s');
        }

        $datosValidados['estado'] = 'programada';
        
        $cita = Cita::create($datosValidados);
        
        // --- CAMBIO: Cargar especialidad al responder ---
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
        
        // --- CAMBIO: Cargar especialidad al ver detalle ---
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);
        
        return new CitaResource($cita);
    }

    /**
     * Actualiza una cita.
     */
    public function update(UpdateCitaRequest $request, Cita $cita)
    {
        Gate::authorize('update', $cita);

        // --- LÓGICA ORIGINAL: Protección de estado ---
        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json(['message' => 'No se puede editar una cita finalizada o cancelada.'], 409);
        }

        $datos = $request->validated();
        
        // --- LÓGICA ORIGINAL: Resetear a 'programada' si cambia fecha ---
        if (isset($datos['fecha_hora_inicio'])) {
             $datos['estado'] = 'programada';
        }

        $cita->update($datos);
        
        // --- CAMBIO: Cargar especialidad al responder ---
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);
        
        return new CitaResource($cita);
    }

    /**
     * Cancela una cita.
     */
    public function destroy(Cita $cita)
    {
        Gate::authorize('delete', $cita);
        
        // --- LÓGICA ORIGINAL: Protección de estado ---
        if ($cita->estado == 'completada') {
             return response()->json(['message' => 'No se puede cancelar una cita que ya ha finalizado.'], 409);
        }

        $cita->update(['estado' => 'cancelada']);
        
        // --- CAMBIO: Cargar especialidad al responder ---
        $cita->load(['paciente.user', 'medico.user', 'medico.especialidad']);
        
        return new CitaResource($cita);
    }
}