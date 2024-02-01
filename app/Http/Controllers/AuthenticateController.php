<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Repository\Authenticate\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;

class AuthenticateController extends Controller
{
    public function __construct(private readonly AuthRepositoryInterface $authRepository)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authRepository->login($request->validated());
        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $token = $this->authRepository->register($request->validated());
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60 * 24 * 365 // one year
        ]);
    }
}
