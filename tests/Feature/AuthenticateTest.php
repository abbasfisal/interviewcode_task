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

        $this->postJson(route('register'), $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);
        $this->assertDatabaseHas(User::class, [
            'name'  => 'alireza',
            'email' => 'alireza@gmail.com',
        ]);
    }

    public function test_login_with_email_password()
    {
        $this->test_register();

        $this->postJson(route('login'), ['email' => 'alireza@gmail.com', 'password' => 'password'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);

    }
}
