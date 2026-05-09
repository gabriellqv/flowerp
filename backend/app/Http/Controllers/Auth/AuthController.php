<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Realiza o login do usuario e retorna o token Sanctum.
     *
     * @param  Request  $request  Dados do login (email e password)
     * @return JsonResponse Token e dados do usuario
     *
     * @throws ValidationException Caso as credenciais sejam invalidas
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais invalidas.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Conta desativada. Contate o administrador.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'access_token' => $user->createToken('flowerp')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Revoga o token atual do usuario logado (logout).
     *
     * @param  Request  $request  Request com o token bearer
     * @return JsonResponse Mensagem de sucesso
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado.']);
    }

    /**
     * Retorna os dados do usuario logado.
     *
     * @param  Request  $request  Request com o token bearer
     * @return JsonResponse Dados basicos do usuario
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only(['id', 'name', 'email', 'role']));
    }
}
