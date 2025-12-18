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

        // --- 3. Buscador Global ---
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                // CORRECCIÓN: Aquí también cambiamos 'usuario' por 'user'
                $q->whereHas('paciente.user', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%");
                })->orWhereHas('medico.user', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%");
                });
            });
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
public function update(Request $request, $id)
    {
        Gate::authorize('update', $cita);

        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json(['message' => 'No se puede editar una cita finalizada o cancelada.'], 409);
        }

        $datos = $request->validated();
        
        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json([
                 'success' => false,
                 'message' => 'No se puede editar una cita finalizada o cancelada.'
             ], 409);
        }

        // 1. Preparamos los datos que vienen del frontend
        $datos = $request->all();

        // 2. LÓGICA CRÍTICA: Si se está editando la fecha/hora de inicio,
        // debemos recalcular obligatoriamente la fecha de fin.
        if ($request->has('fecha_hora_inicio')) {
            try {
                $inicio = \Carbon\Carbon::parse($datos['fecha_hora_inicio']);
                // Recalculamos el fin (30 min después del nuevo inicio)
                $datos['fecha_hora_fin'] = $inicio->copy()->addMinutes(30);
            } catch (\Exception $e) {
                // Si la fecha viene mal formato, ignoramos o lanzamos error
            }
        }

        // 3. Actualizamos con los datos procesados (incluyendo la nueva fecha fin)
        $cita->update($datos);

        return response()->json([
            'success' => true,
            'data'    => $cita,
            'message' => 'Cita actualizada correctamente'
        ]);
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

    public function listarPacientes()
    {
        // Traemos todos los pacientes con su usuario asociado
        $pacientes = \App\Models\Paciente::with('user:id,nombre,apellidos')->get();

        // Transformamos la data para enviar solo lo necesario: ID del PACIENTE y Nombre Completo
        $lista = $pacientes->map(function($paciente) {
            return [
                'id' => $paciente->id, // Este es el ID que guardaremos en la cita
                'nombre' => $paciente->user 
                    ? $paciente->user->nombre . ' ' . $paciente->user->apellidos 
                    : 'Usuario Desconocido'
            ];
        });

        return response()->json($lista);
    }
    public function listarMedicos()
    {
        $medicos = \App\Models\Medico::with('user:id,nombre,apellidos')->get();

        $lista = $medicos->map(function($medico) {
            return [
                'id' => $medico->id, // Este es el ID del MEDICO (no del usuario)
                'nombre' => $medico->user 
                    ? 'Dr(a). ' . $medico->user->nombre . ' ' . $medico->user->apellidos 
                    : 'Médico Desconocido'
            ];
        });

        return response()->json($lista);
    }    
}