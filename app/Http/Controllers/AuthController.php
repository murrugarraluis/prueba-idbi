<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }

        // Obtener el usuario autenticado
        $user = JWTAuth::user();

        return response()->json([
            'token' => $token,
            'id' => $user->id,         // Agregar el ID del usuario
            'name' => $user->name      // Agregar el nombre del usuario
        ]);
    }


    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $token = JWTAuth::setToken($token)->getToken();
            JWTAuth::manager()->invalidate($token, true);
            return response()->json(['message' => 'Logout exitoso'], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
