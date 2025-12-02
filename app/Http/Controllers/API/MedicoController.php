<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicoResource;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{
    /**
     * Muestra el directorio de médicos con estadísticas completas
     * para el dashboard de "Gestión de Médicos".
     */
    public function index(Request $request)
    {
        // --- 1. Obtener Estadísticas Agregadas (Tarjetas Superiores) ---
        $totalMedicos = Medico::count();
        $totalEspecialidades = Especialidad::count();
        $totalCitas = Cita::count();
        $totalPacientesAtendidos = Cita::distinct('paciente_id')->count('paciente_id');

        $statsGenerales = [
            'totalMedicos' => $totalMedicos,
            'totalEspecialidades' => $totalEspecialidades,
            'totalCitas' => $totalCitas,
            'totalPacientesAtendidos' => $totalPacientesAtendidos,
        ];

        // --- 2. Obtener Directorio de Médicos (Lista Principal) ---
        $query = Medico::query()->with(['user', 'especialidad']);

        // Cargar estadísticas individuales para CADA médico
        $query->withCount([
            'citas', // citas_count
            'citas as citas_completadas_count' => function ($q) {
                $q->where('estado', 'completada');
            },
            'citas as citas_pendientes_count' => function ($q) {
                // Estado 'programada' o 'confirmada'
                $q->whereIn('estado', ['programada', 'confirmada']); 
            },
            // Contar pacientes únicos por médico
            'citas as pacientes_atendidos_count' => function ($q) {
                $q->select(DB::raw('count(distinct paciente_id)'));
            }
        ]);

        // --- 3. Aplicar Búsqueda ---
        // (Buscar por nombre de médico o nombre de especialidad)
        if ($q = $request->get('q')) {
            $query->whereHas('user', function ($subQuery) use ($q) {
                $subQuery->where('nombre', 'like', "%{$q}%")
                         ->orWhere('apellidos', 'like', "%{$q}%");
            })
            ->orWhereHas('especialidad', function ($subQuery) use ($q) {
                $subQuery->where('nombre', 'like', "%{$q}%");
            });
        }
        
        $perPage = (int) $request->get('per_page', 10);
        $medicos = $query->paginate($perPage);

        // --- 4. Devolver Respuesta ---
        // Usamos MedicoResource para formatear la lista
        // y .additional() para adjuntar las estadísticas generales
        
        return MedicoResource::collection($medicos)
            ->additional([
                'meta' => [
                    'stats_generales' => $statsGenerales
                ]
            ]);
    }

    public function misPacientes(Request $request)
    {
        $user = $request->user();
        
        if (!$user->medico) {
            return response()->json(['message' => 'No eres un médico.'], 403);
        }

        // Buscamos en la tabla Citas los pacientes únicos de este médico
        $pacientes = \App\Models\Cita::where('medico_id', $user->medico->id)
            ->with('paciente.user') // Cargar datos del paciente
            ->get()
            ->pluck('paciente')     // Extraer solo el objeto paciente
            ->unique('id')          // Eliminar duplicados (si atendió al mismo 2 veces)
            ->values();             // Reindexar array

        return response()->json([
            'data' => $pacientes->map(function($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->user->nombre_completo,
                    'email' => $p->user->email,
                    'telefono' => $p->telefono,
                    'edad' => \Carbon\Carbon::parse($p->fecha_nacimiento)->age,
                    'ultima_visita' => $p->citas()->latest()->first()->fecha_hora_inicio ?? null
                ];
            })
        ]);
    }

    // NOTA: Las acciones de Crear, Editar y Borrar médicos
    // las seguiremos manejando con UserController (POST /users, PUT /users/{id})
    // ya que involucran 2 tablas (users y medicos).
    /**
     * Devuelve los contadores para el Dashboard del Médico.
     */
    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        
        if (!$user->medico) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        $medicoId = $user->medico->id;
        $hoy = \Carbon\Carbon::today();

        // 1. Citas Hoy (Total del día, sin importar estado)
        $citasHoy = \App\Models\Cita::where('medico_id', $medicoId)
            ->whereDate('fecha_hora_inicio', $hoy)
            ->count();

        // 2. Pendientes (Total histórico en estado 'programada')
        $pendientes = \App\Models\Cita::where('medico_id', $medicoId)
            ->where('estado', 'programada')
            ->count();

        // 3. Próximas Citas (Programadas a futuro, a partir de mañana)
        $proximas = \App\Models\Cita::where('medico_id', $medicoId)
            ->where('estado', 'programada')
            ->whereDate('fecha_hora_inicio', '>', $hoy)
            ->count();

        // 4. Pacientes Únicos (Total histórico)
        $pacientesUnicos = \App\Models\Cita::where('medico_id', $medicoId)
            ->distinct('paciente_id')
            ->count('paciente_id');

        return response()->json([
            'citas_hoy' => $citasHoy,
            'pendientes_confirmacion' => $pendientes,
            'proximas_citas' => $proximas,
            'total_pacientes' => $pacientesUnicos
        ]);
    }
}