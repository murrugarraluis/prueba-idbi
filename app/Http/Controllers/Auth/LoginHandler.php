<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginHandler
{
    public function __invoke(LoginRequest $request): Response
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response([
                    'message' => 'Credenciales inválidos'
                ], 401);
            }
        } catch (JWTException $exception) {
            return response([
                'message' => 'No se pudo crear el token'
            ], 500);
        }

        $user = JWTAuth::user();

        return response([
            'token' => $token,
            'id' => $user->id,
            'name' => $user->name
        ], 200);
    }
}
