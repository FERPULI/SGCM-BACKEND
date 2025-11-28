<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // Asegúrate de que este Request exista o usa Request estándar
use App\Http\Requests\LoginRequest;    // Asegúrate de que este Request exista
use App\Http\Requests\UpdateProfileRequest; // Asegúrate de que este Request exista
use Illuminate\Support\Facades\Hash;
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

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['rol']); // no permitir cambiar rol desde aquí
        $user->update($data);
        
        // Refrescamos y cargamos relaciones para devolver el objeto actualizado completo
        $user->refresh(); 
        $user->load(['paciente', 'medico']);

        return response()->json(['user' => new UserResource($user)], 200);
    }
}