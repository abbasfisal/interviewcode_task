<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateController extends Controller
{
    public function login(LoginRequest $request)
    {
    }

    public function register(RegisterRequest $request)
    {

        $data['password'] = bcrypt($request->get('password'));
        dd($data);
        $user = User::create($data);
        $accessToken = $user->createToken('UserToken')->accessToken;
        return response()->json([
                                    'user'       => new UserResource($user),
                                    'token'      => $accessToken,
                                    'token_type' => 'Bearer'
                                ]);
    }
}
