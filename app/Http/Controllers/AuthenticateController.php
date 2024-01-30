<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class AuthenticateController extends Controller
{
    public function login(LoginRequest $request)
    {
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
