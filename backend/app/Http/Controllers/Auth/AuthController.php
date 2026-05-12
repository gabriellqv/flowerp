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

    /**
     * Atualiza os dados de perfil do usuario logado.
     *
     * @param  Request  $request  Request com nome e email
     * @return JsonResponse Dados atualizados do usuario
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
        ]);

        $request->user()->update($request->only(['name', 'email']));

        return response()->json($request->user()->only(['id', 'name', 'email', 'role']));
    }

    /**
     * Atualiza a senha do usuario logado.
     *
     * Exige confirmacao da senha atual antes de permitir a troca.
     *
     * @param  Request  $request  Request com current_password e password
     * @return JsonResponse Mensagem de sucesso
     *
     * @throws ValidationException Caso a senha atual esteja incorreta
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Senha atual incorreta.'],
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }
}
