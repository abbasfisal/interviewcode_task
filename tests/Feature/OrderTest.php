<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function clearProducts(): void
    {
        User::query()->forceDelete();
        Product::query()->forceDelete();
        Order::query()->forceDelete();
    }


    public function test_unauthenticated()
    {
        $this->clearProducts();
        $this->getJson(route('orders.index'))
            ->assertUnauthorized();
    }

}
