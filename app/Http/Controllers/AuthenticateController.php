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

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($request->get('password'));

        /** @var User $user */
        $user = User::query()->create($data);

        $accessToken = $user->createToken('UserToken');

        return response()->json([
            'user'       => $user->toArray(),
            'token'      => $accessToken,
            'token_type' => 'Bearer'
        ]);
    }
}
