<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
// Asegúrate de tener estos Mails creados, si no, comenta las líneas de Mail::to
use App\Mail\NuevaCitaMail;
use App\Mail\CitaConfirmadaMail; 

class CitaController extends Controller
{
    /**
     * Muestra lista de citas + Estadísticas para el Dashboard.
     */
    public function index(Request $request)
    {
        // Si no tienes Policies creadas, comenta esta línea para evitar error 403
        // Gate::authorize('viewAny', Cita::class);

        $user = Auth::user();
        
        // 1. Eager Loading (Carga optimizada de relaciones)
        // NOTA: Usamos 'usuario' porque así confirmamos que se llama la relación en tu modelo
        $query = Cita::with([
            'paciente.user:id,nombre,apellidos,email', 
            'medico.user:id,nombre,apellidos,email', 
            'medico.especialidad:id,nombre'
        ]);

        // --- 2. Filtro por ROL (Seguridad) ---
        if ($user->hasRole('paciente')) {
            $query->where('paciente_id', $user->paciente->id);
        } elseif ($user->hasRole('medico')) {
            $query->where('medico_id', $user->medico->id);
        }

        // --- 3. Buscador Global ---
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('paciente.user', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%");
                })->orWhereHas('medico.user', function ($subQ) use ($search) {
                    $subQ->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%");
                });
            });
        }

        // --- 4. Filtros de ESTADO ---
        if ($status = $request->input('status')) {
            if ($status !== 'todas' && $status !== null) {
                if ($status === 'activas') {
                    $query->whereIn('estado', ['programada', 'confirmada']);
                } else {
                    $query->where('estado', $status);
                }
            }
        }

        // --- 5. Ordenamiento y Paginación ---
        $citas = $query->orderBy('fecha_hora_inicio', 'desc')->paginate(10);

        // --- 6. ESTADÍSTICAS ---
        $stats = [
            'total'       => Cita::count(),
            'activas'     => Cita::whereIn('estado', ['programada', 'confirmada'])->count(),
            'pendientes'  => Cita::where('estado', 'programada')->count(),
            'completadas' => Cita::where('estado', 'completada')->count(),
            'canceladas'  => Cita::where('estado', 'cancelada')->count(),
        ];

        // --- 7. RESPUESTA JSON FINAL ---
        return response()->json([
            'success' => true,
            'data'    => $citas,
            'stats'   => $stats,
            'message' => 'Citas obtenidas correctamente'
        ]);
    }

    /**
     * Almacena una nueva cita.
     */
    public function store(Request $request)
    {
        // Validación básica
        $datos = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_id'   => 'required|exists:medicos,id',
            'fecha_hora_inicio' => 'required|date',
            'motivo_consulta' => 'nullable|string'
        ]);

        // Cálculo automático de fin (30 min)
        $inicio = \Carbon\Carbon::parse($datos['fecha_hora_inicio']);
        $datos['fecha_hora_fin'] = $inicio->copy()->addMinutes(30);
        $datos['estado'] = 'programada';
        
        $cita = Cita::create($datos);
        
        // Intentar enviar correo (dentro de try/catch para no fallar si no hay config de mail)
        try {
            $cita->load('paciente.usuario');
            if ($cita->paciente && $cita->paciente->usuario) {
                Mail::to($cita->paciente->usuario->email)->send(new NuevaCitaMail($cita));
            }
        } catch (\Exception $e) {
            // Log::error("Error enviando correo: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data'    => $cita,
            'message' => 'Cita creada exitosamente'
        ], 201);
    }

    /**
     * Muestra una cita específica.
     */
    public function show($id)
    {
        $cita = Cita::with(['paciente.usuario', 'medico.usuario', 'medico.especialidad'])->find($id);

        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data'    => $cita
        ]);
    }

    /**
     * Actualiza una cita.
     */
    public function update(Request $request, $id)
    {
        $cita = Cita::find($id);
        
        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        // Protección: No editar citas ya cerradas
        if (in_array($cita->estado, ['cancelada', 'completada'])) {
             return response()->json([
                 'success' => false,
                 'message' => 'No se puede editar una cita finalizada o cancelada.'
             ], 409);
        }

        // 1. Tomamos los datos que envía el frontend
        $datos = $request->all();

        // 2. LÓGICA DE RECALCULO:
        // Si el usuario cambió la fecha/hora de inicio, debemos recalcular cuándo termina la cita.
        if ($request->has('fecha_hora_inicio')) {
            try {
                // Parseamos la fecha texto a objeto Carbon
                $inicio = \Carbon\Carbon::parse($datos['fecha_hora_inicio']);
                
                // Calculamos el fin (30 minutos después del inicio)
                $datos['fecha_hora_fin'] = $inicio->copy()->addMinutes(30);
                
            } catch (\Exception $e) {
                // Si la fecha viniera vacía o corrupta, Laravel fallará en el update, 
                // pero aquí evitamos que el cálculo rompa el código.
            }
        }

        // 3. Actualizamos la cita con los datos procesados
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
    public function destroy($id)
    {
        $cita = Cita::find($id);

        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }
        
        if ($cita->estado == 'completada') {
             return response()->json([
                 'success' => false, 
                 'message' => 'No se puede cancelar una cita que ya ha finalizado.'
             ], 409);
        }

        $cita->update(['estado' => 'cancelada']);
        
        return response()->json([
            'success' => true,
            'data'    => $cita,
            'message' => 'Cita cancelada correctamente'
        ]);
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

    /**
     * Obtener lista simple de médicos para selectores
     */
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
