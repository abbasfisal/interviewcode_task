<?php

namespace App\Repository\Order;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function store(array $data);

    public function index();

    public function destroy(Order $order);

}
