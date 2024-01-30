<?php

namespace App\Repository\Order;

interface OrderRepositoryInterface
{
    public function store(array $data);

    public function index();
}
