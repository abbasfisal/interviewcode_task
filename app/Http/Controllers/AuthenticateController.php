<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthenticateController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = bcrypt($request->get('password'));

        /** @var User $user */
        $user = User::query()->create($data);

        if (!$token = auth()->attempt(['email' => $request->get('email'), 'password' => $request->get('password')])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);

    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
