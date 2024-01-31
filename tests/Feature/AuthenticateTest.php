<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function clearProducts(): void
    {
        User::query()->forceDelete();
        Product::query()->forceDelete();
    }

    public function test_register()
    {
        $this->clearProducts();
        $data = [
            'name'                  => 'alireza',
            'email'                 => 'alireza@gmail.com',
            'password'              => 'password',
            'password_confirmation' => 'password'
        ];

        $response = $this->postJson(route('register'), $data)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);
    }
}
