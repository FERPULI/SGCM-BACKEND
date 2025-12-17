<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // Asegúrate de que este Request exista o usa Request estándar
use App\Http\Requests\LoginRequest;    // Asegúrate de que este Request exista
use App\Http\Requests\UpdateProfileRequest; // Asegúrate de que este Request exista
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        // =================================================================
        // 🔥 CORRECCIÓN CLAVE: Cargar Paciente y Médico
        // =================================================================
        // Esto asegura que el UserResource incluya los objetos anidados
        $user->load(['paciente', 'medico']);

        $token = $user->createToken($data['device_name'] ?? 'api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user), // Ahora este user lleva el paciente dentro
            'token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = $data['activo'] ?? true;

        $user = User::create($data);
        
        // Intentamos cargar relaciones (aunque al registrarse recién estarán vacías,
        // esto evita errores si tu lógica cambia en el futuro)
        $user->load(['paciente', 'medico']);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Se revocaron todos los tokens.'], 200);
    }

    public function profile(Request $request)
    {
        // 🔥 CORRECCIÓN: Cargar relaciones también al consultar el perfil
        $user = $request->user();
        $user->load(['paciente', 'medico']);

        return response()->json(['user' => new UserResource($user)], 200);
    }

public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validamos los datos
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            // El email debe ser único, pero ignorando el ID del usuario actual
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        // Actualizamos la tabla users
        /** @var \App\Models\User $user */
        $user->update([
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed', // 'confirmed' busca un campo new_password_confirmation
        ]);

        $user = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual no es correcta.'
            ], 400);
        }

        // Actualizar contraseña
        /** @var \App\Models\User $user */
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente.'
        ]);
    }
}