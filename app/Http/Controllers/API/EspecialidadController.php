<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use App\Http\Resources\EspecialidadResource;
use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    /**
     * Muestra una lista de todas las especialidades.
     */
    public function index(Request $request)
    {
        // Búsqueda por nombre
        if ($q = $request->get('q')) {
            $query = Especialidad::where('nombre', 'like', "%{$q}%");
        } else {
            $query = Especialidad::query();
        }

        // Paginación (opcional, pero buena práctica)
        $perPage = (int) $request->get('per_page', 15);
        $especialidades = $query->orderBy('nombre', 'asc')->paginate($perPage);

        return EspecialidadResource::collection($especialidades);
    }

    /**
     * Almacena una nueva especialidad en la base de datos.
     */
    public function store(StoreEspecialidadRequest $request)
    {
        $especialidad = Especialidad::create($request->validated());

        return (new EspecialidadResource($especialidad))
            ->response()
            ->setStatusCode(201); // 201 Created
    }

    /**
     * Muestra una especialidad específica.
     * (Usa Route-Model Binding)
     */
    public function show(Especialidad $especialidad)
    {
        return new EspecialidadResource($especialidad);
    }

    /**
     * Actualiza una especialidad específica.
     * (Usa Route-Model Binding)
     */
    public function update(UpdateEspecialidadRequest $request, Especialidad $especialidade)
    {
        $especialidade->update($request->validated());

        return new EspecialidadResource($especialidade);
    }

    /**
     * Elimina una especialidad.
     * (Usa Route-Model Binding)
     */
    public function destroy(Especialidad $especialidade)
    {
        // **IMPORTANTE: Validación de Clave Foránea**
        // Tu SQL no usa ON DELETE CASCADE. Si borramos una especialidad
        // que tiene médicos asignados, la BD dará un error de integridad.
        // Verificamos si hay médicos usándola.
        
        if ($especialidade->medicos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la especialidad porque está siendo utilizada por uno o más médicos.'
            ], 409); // 409 Conflict
        }

        $especialidade->delete();

        // Seguimos el patrón de tu UserController
        return response()->json(['message' => 'Especialidad eliminada correctamente.'], 200); 
    }
}