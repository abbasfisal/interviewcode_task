<?php

namespace App\Repository\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly Order $order)
    {
    }

    public function index(): Collection|array
    {

    }

    public function store(array $data)
    {

    }

    public function update(Order $product, array $data)
    {
    }

    public function destory(Order $product)
    {

    }
}
