<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    public function test_store_order()
    {
        $this->clearProducts();

        $products = Product::factory(3)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $response = $this
            ->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'name'       => $products[0]['name'],
                            'count'      => $firstOrderCount,
                            'price'      => $products[0]['price'],
                        ],
                        [
                            'product_id' => $products[1]['_id'],
                            'name'       => $products[1]['name'],
                            'count'      => $secondOrderCount,
                            'price'      => $products[1]['price'],
                        ]
                    ]
                ]
            );

        $this->assertDatabaseCount(Product::class, 3);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'inventory' => $products[0]['inventory'] - $firstOrderCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'name' => $products[1]['name'], 'inventory' => $products[1]['inventory'] - $secondOrderCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[2]['_id'], 'name' => $products[2]['name'], 'inventory' => $products[2]['inventory']]);

        $order = Order::query()->where('user_id', Auth::id())->get()->toArray();
        $this->assertDatabaseHas(Order::class, [

            'user_id'     => Auth::id(),
            'products'    => [
                [
                    'product_id' => $products[0]['_id'],
                    'name'       => $products[0]['name'],
                    'count'      => $firstOrderCount,
                    'price'      => $products[0]['price']
                ],
                [
                    'product_id' => $products[1]['_id'],
                    'name'       => $products[1]['name'],
                    'count'      => $secondOrderCount,
                    'price'      => $products[1]['price']
                ]
            ],
            'total_price' => ($products[0]['price'] * $firstOrderCount) + ($products[1]['price'] * $secondOrderCount)
        ]);

    }
}
