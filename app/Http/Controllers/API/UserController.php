<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest; // Asegúrate de tener este archivo para 'update'
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Paciente; // <-- Importado
use App\Models\Medico;   // <-- Importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <-- Importado
use Exception;                     // <-- Importado

class UserController extends Controller
{
    /**
     * Muestra una lista de usuarios con filtros avanzados.
     *
     * ESTE ES EL MÉTODO ACTUALIZADO
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        
        // 1. Iniciar la consulta CON las relaciones
        // Esto evita N+1 queries al generar el UserResource
        $query = User::query()->with(['paciente', 'medico']);

        // 2. Filtrar por ROL
        // Si se envía ?rol=paciente, filtra por "paciente".
        // Si no se envía 'rol', no filtra (muestra todos).
        if ($rol = $request->get('rol')) {
            $query->where('rol', $rol);
        }

        // 3. Búsqueda General (parámetro ?q=...)
        // Aquí está la nueva lógica con orWhereHas
        if ($q = $request->get('q')) {
            $query->where(function($mainQuery) use ($q) {
                
                // A. Buscar en la tabla 'users' (nombre, apellidos, email)
                $mainQuery->where('nombre', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                
                // B. Buscar en la tabla 'pacientes' (telefono, direccion)
                // orWhereHas -> Busca usuarios que TENGAN un paciente que coincida
                $mainQuery->orWhereHas('paciente', function($pacienteQuery) use ($q) {
                    $pacienteQuery->where('telefono', 'like', "%{$q}%")
                                    ->orWhere('direccion', 'like', "%{$q}%");
                });

                // C. Buscar en la tabla 'medicos' (licencia, telefono)
                // orWhereHas -> Busca usuarios que TENGAN un médico que coincida
                $mainQuery->orWhereHas('medico', function($medicoQuery) use ($q) {
                    $medicoQuery->where('licencia_medica', 'like', "%{$q}%")
                                ->orWhere('telefono_consultorio', 'like', "%{$q}%");
                });
            });
        }

        // 4. Ordenar y Paginar
        // Ordenamos por nombre A-Z
        $users = $query->orderBy('nombre', 'asc')->paginate($perPage);

        // 5. Retornar la colección de recursos
        return UserResource::collection($users)->response();
    }

    /**
     * Muestra un usuario específico.
     */
    public function show($id)
    {
        // Cargar perfiles al mostrar un usuario
        $user = User::with(['paciente', 'medico'])->findOrFail($id);
        return new UserResource($user);
    }

    /**
     * Almacena un nuevo usuario y su perfil (paciente o médico) en la base de datos.
     */
    public function store(StoreUserRequest $request)
    {
        // Los datos ya vienen validados por StoreUserRequest
        $datosValidados = $request->validated();

        try {
            // 1. Iniciar la transacción
            $user = DB::transaction(function () use ($datosValidados) {

                // 2. Crear el Usuario (tabla 'users')
                $user = User::create([
                    'nombre' => $datosValidados['nombre'],
                    'apellidos' => $datosValidados['apellidos'],
                    'email' => $datosValidados['email'],
                    'password' => Hash::make($datosValidados['password']),
                    'rol' => $datosValidados['rol'],
                    'activo' => $datosValidados['activo'] ?? true, // Valor por defecto
                ]);

                // 3. Crear el Perfil Específico usando la relación
                if ($datosValidados['rol'] === 'paciente') {

                    $user->paciente()->create([
                        'fecha_nacimiento' => $datosValidados['fecha_nacimiento'] ?? null,
                        'telefono' => $datosValidados['telefono'] ?? null,
                        'direccion' => $datosValidados['direccion'] ?? null,
                        'tipo_sangre' => $datosValidados['tipo_sangre'] ?? null,
                        'alergias' => $datosValidados['alergias'] ?? null,
                    ]);

                } elseif ($datosValidados['rol'] === 'medico') {

                    $user->medico()->create([
                        'especialidad_id' => $datosValidados['especialidad_id'], // Requerido por la validación
                        'licencia_medica' => $datosValidados['licencia_medica'] ?? null,
                        'telefono_consultorio' => $datosValidados['telefono_consultorio'] ?? null,
                        'biografia' => $datosValidados['biografia'] ?? null,
                    ]);
                }
                // Si es 'admin', no se crea perfil adicional.

                return $user;
            });

            // 4. Si todo va bien, cargar las relaciones y retornar
            // Usamos load() para incluir el perfil recién creado en la respuesta
            $user->load('paciente', 'medico');

            return (new UserResource($user))
                ->response()
                ->setStatusCode(201); // 201 Created

        } catch (Exception $e) {
            // 5. Si algo falla, la transacción hace rollback
            return response()->json([
                'message' => 'Error al registrar el usuario.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Actualiza un usuario específico Y su perfil asociado.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::with(['paciente', 'medico'])->findOrFail($id);
        
        $datosValidados = $request->validated();

        try {
            // 1. Iniciar la transacción
            DB::transaction(function () use ($user, $datosValidados) {

                // 2. Preparar y Actualizar Usuario (tabla 'users')
                
                // Manejo especial de contraseña
                if (!empty($datosValidados['password'])) {
                    $datosValidados['password'] = Hash::make($datosValidados['password']);
                } else {
                    unset($datosValidados['password']); // No actualizar si está vacío
                }
                
                $user->update($datosValidados);

                // 3. Actualizar Perfil (si existe)
                // El update() de Eloquent es inteligente: solo actualizará
                // los campos que existan en $datosValidados Y en el $fillable del modelo.
                
                if ($user->rol === 'paciente' && $user->paciente) {
                    $user->paciente->update($datosValidados);
                } 
                elseif ($user->rol === 'medico' && $user->medico) {
                    $user->medico->update($datosValidados);
                }
                
            }); // 4. Fin de la transacción

            // 5. Recargar relaciones y devolver
            $user->refresh()->load(['paciente', 'medico']);

            return new UserResource($user);

        } catch (Exception $e) {
            // 6. Si algo falla, hacer rollback
            return response()->json([
                'message' => 'Error al actualizar el usuario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un usuario.
     * (NOTA: Gracias a 'ON DELETE CASCADE' en tu SQL,
     * al borrar un User, se borrará su perfil de paciente o medico)
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente.'], 200);
    }

    /**
     * Obtiene los conteos de usuarios por rol y el total.
     */
    public function getCounts()
    {
        $totalUsers = User::count();
        $pacientesCount = User::where('rol', 'paciente')->count();
        $medicosCount = User::where('rol', 'medico')->count();
        $administradoresCount = User::where('rol', 'admin')->count();
        
        // Puedes agregar más roles si los tienes
        // $recepcionistasCount = User::where('rol', 'recepcion')->count();

        return response()->json([
            'total_usuarios' => $totalUsers,
            'pacientes' => $pacientesCount,
            'medicos' => $medicosCount,
            'administradores' => $administradoresCount,
            // 'recepcionistas' => $recepcionistasCount, // Si agregas este
        ]);
    }
}