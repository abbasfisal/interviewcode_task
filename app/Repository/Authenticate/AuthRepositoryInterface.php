<?php

namespace App\Repository\Authenticate;

interface AuthRepositoryInterface
{

    public function login(array $data);

    public function register(array $data);
}
