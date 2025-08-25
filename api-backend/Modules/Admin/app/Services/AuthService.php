<?php

namespace Modules\Admin\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Admin\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Registra usuário e já retorna um JWT.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'cpf'      => $data['cpf'],
            'password' => Hash::make($data['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->tokenResponse($token, $user);
    }


    /**
     * Faz login e retorna JWT.
     */
    public function login(array $data): array
    {
        $credentials = [
            'email'    => $data['email']    ?? null,
            'password' => $data['password'] ?? null,
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas são inválidas.'],
            ]);
        }

        return $this->tokenResponse($token, auth('api')->user());
    }
    /**
     * Retorna o usuário autenticado.
     */
    public function me(): array
    {
        return ['user' => auth('api')->user()];
    }

    /**
     * Invalida o token atual (blacklist).
     */
    public function logout(): void
    {
        auth('api')->logout();
    }

    /**
     * Gera um novo token a partir do atual.
     */
    public function refresh(): array
    {
        $token = auth('api')->refresh();

        return $this->tokenResponse($token, auth('api')->user());
    }

    /**
     * Padroniza o payload de resposta do token.
     */
    private function tokenResponse(string $token, User $user): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // em segundos
            'user'         => $user,
        ];
    }
}

