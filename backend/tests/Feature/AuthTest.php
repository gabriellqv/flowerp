<?php

/*
|--------------------------------------------------------------------------
| Testes de autenticacao (login, logout, sessao).
|
| Valida o fluxo completo de autenticacao via Sanctum:
| credenciais validas, invalidas, conta desativada,
| acesso a rotas protegidas e revogacao de token.
|--------------------------------------------------------------------------
*/

use App\Models\User;

test('login retorna token e dados do usuario com credenciais validas', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'password' => 'senha123',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'senha123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'access_token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('user.role', 'admin');
});

test('login atualiza last_login_at do usuario', function () {
    $user = User::factory()->create(['password' => 'senha123']);

    expect($user->last_login_at)->toBeNull();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'senha123',
    ])->assertOk();

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

test('login falha com senha incorreta', function () {
    $user = User::factory()->create(['password' => 'senha123']);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'senha_errada',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login falha com email inexistente', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'fantasma@flowerp.com',
        'password' => 'senha123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login falha com conta desativada', function () {
    $user = User::factory()->create([
        'is_active' => false,
        'password' => 'senha123',
    ]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'senha123',
    ])
        ->assertStatus(422)
        ->assertJsonFragment(['Conta desativada. Contate o administrador.']);
});

test('login requer email e senha', function () {
    $this->postJson('/api/auth/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login rejeita email com formato invalido', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'nao-e-email',
        'password' => 'senha123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('rota protegida rejeita requisicao sem token', function () {
    $this->getJson('/api/auth/me')
        ->assertStatus(401);
});

test('rota /auth/me retorna dados do usuario autenticado', function () {
    $user = User::factory()->create(['role' => 'manager']);

    $this->actingAs($user)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $user->id,
            'email' => $user->email,
            'role' => 'manager',
        ]);
});

test('logout revoga o token do usuario', function () {
    $user = User::factory()->create();

    // Cria token real via Sanctum
    $token = $user->createToken('flowerp')->plainTextToken;

    // Faz logout com o token
    $this->withToken($token)
        ->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonFragment(['message' => 'Logout realizado.']);

    // Verifica que nao existem mais tokens para o usuario
    expect($user->tokens()->count())->toBe(0);
});
