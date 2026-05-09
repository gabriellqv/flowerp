<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Rotas Publicas (Sem token)
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas Protegidas (Exigem token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // --- Rotas de Teste do RBAC ---

    // Qualquer usuario logado acessa
    Route::get('/test/anyone', function () {
        return response()->json(['message' => 'Qualquer role acessa aqui.']);
    });

    // Apenas Admin e Gerente acessam
    Route::get('/test/management', function () {
        return response()->json(['message' => 'So admin e manager.']);
    })->middleware('role:admin,manager');

    // Apenas Admin acessa
    Route::get('/test/admin-only', function () {
        return response()->json(['message' => 'So admin.']);
    })->middleware('role:admin');
});
