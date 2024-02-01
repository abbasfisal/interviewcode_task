<?php

namespace App\Repository\Authenticate;

use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{

    public function login(array $data)
    {
        if (!$token = auth()->attempt($data)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $token;
    }

    public function register(array $data)
    {
        /** @var User $user */
        User::query()->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        if (!$token = auth()->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $token;
    }
}
