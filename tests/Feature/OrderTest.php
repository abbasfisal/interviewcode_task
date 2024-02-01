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

        $products = Product::factory(2)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this
            ->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderCount,
                        ],
                        [
                            'product_id' => $products[1]['_id'],
                            'count'      => $secondOrderCount,
                        ]
                    ]
                ]
            );

        $calculateProductOneInventory = $products[0]['inventory'] - $firstOrderCount;
        $calculateProductTowInventory = $products[1]['inventory'] - $secondOrderCount;

        $this->assertDatabaseCount(Product::class, 2);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'inventory' => $calculateProductOneInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'name' => $products[1]['name'], 'inventory' => $calculateProductTowInventory]);

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

    public function test_out_of_stock()
    {

        $this->clearProducts();

        $products = Product::factory(3)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this->actingAs(User::factory()->create())
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
                            'count'      => $secondOrderCount * 5000,
                            'price'      => $products[1]['price'],
                        ]
                    ]
                ]
            )
            ->assertStatus(422);

    }

    public function test_index()
    {
        $this->clearProducts();
        $loginUser = User::factory()->create();
        $products = Product::factory(3)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this->actingAs($loginUser)
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

        $order = Order::query()->where('user_id', Auth::id())->first();

        $this->actingAs($loginUser)
            ->getJson(route('orders.index'))
            ->assertStatus(200)
            ->assertJson([
                'message' => 'order lists',
                'data'    => [
                    [
                        'order_id'    => $order->_id,
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
                        'total_price' => $order->total_price
                    ]
                ]
            ]);
    }

    public function test_unauthorized_show()
    {
        $this->clearProducts();
        $loginUser = User::factory()->create();
        $products = Product::factory(3)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this->actingAs($loginUser)
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

        $order = Order::query()->where('user_id', Auth::id())->first();

        $this->actingAs(User::factory()->create())
            ->get(route('orders.show', $order->_id))
            ->assertStatus(403);

    }

    public function test_show()
    {
        $this->clearProducts();
        $loginUser = User::factory()->create();
        $products = Product::factory(3)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this->actingAs($loginUser)
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

        $order = Order::query()->where('user_id', Auth::id())->first();

        $this->actingAs($loginUser)
            ->get(route('orders.show', $order->_id))
            ->assertJson([
                'message' => 'order show',
                'data'    => [
                    'order_id'    => $order->_id,
                    'products'    => [
                        [
                            'product_id' => $products[0]['_id'],
                            'name'       => $products[0]['name'],
                            'price'      => $products[0]['price'],
                            'count'      => $firstOrderCount,
                        ],
                        [
                            'product_id' => $products[1]['_id'],
                            'name'       => $products[1]['name'],
                            'price'      => $products[1]['price'],
                            'count'      => $secondOrderCount,
                        ]
                    ],
                    'total_price' => $order->total_price
                ]
            ]);
    }

    public function test_unauthorized_delete()
    {
        $this->clearProducts();
        $loginUser = User::factory()->create();
        $products = Product::factory(2)->create()->toArray();

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);

        $this->actingAs($loginUser)
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

        $order = Order::query()->where('user_id', Auth::id())->first();

        $this->actingAs(User::factory()->create())
            ->deleteJson(route('orders.destroy', $order->_id))
            ->assertStatus(403);

    }

    public function test_destroy()
    {
        $this->clearProducts();
        $loginUser = User::factory()->create();
        $products = Product::factory(2)->create();

        $oldProductOneInventory = $products[0]->inventory;
        $oldProductTowInventory = $products[1]->inventory;

        $firstOrderCount = rand(1, 9);
        $secondOrderCount = rand(1, 9);
        dump($oldProductOneInventory, $oldProductTowInventory, $firstOrderCount, $secondOrderCount);
        $this->actingAs($loginUser)
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]->_id,
                            'name'       => $products[0]->name,
                            'count'      => $firstOrderCount,
                            'price'      => $products[0]->price,
                        ],
                        [
                            'product_id' => $products[1]->_id,
                            'name'       => $products[1]->name,
                            'count'      => $secondOrderCount,
                            'price'      => $products[1]->price,
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]->_id, 'inventory' => $oldProductOneInventory - $firstOrderCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]->_id, 'inventory' => $oldProductTowInventory - $secondOrderCount]);

        $order = Order::query()->where('user_id', Auth::id())->first();


        $this->actingAs($loginUser)
            ->deleteJson(route('orders.destroy', $order->_id))
            ->assertJson([
                'message' => 'delete successfully',
            ]);

        $this->assertSoftDeleted(Order::class, ['_id' => $order->_id]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]->_id, 'inventory' => $oldProductOneInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]->_id, 'inventory' => $oldProductTowInventory]);
    }

    public function test_update_order_with_equals_order_count_3_3()
    {
        // update with equal => 3 = 3
        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(1)->create(['inventory' => 20])->toArray();

        dump('--- before products', $products);

        $firstOrderProductCount = 3;
        dump('---first order count', $firstOrderProductCount);

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]

                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

        ///////--------------------------------update order
        $updateFirstOrderProductCount = 3; // 3 = 3

       $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[0]['_id'],
                    'count'      => $updateFirstOrderProductCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $updateFirstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);
      $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);
    }

    public function test_update_order_with_new_order_and_positive_count()
    {
        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(3)->create(['inventory' => 20])->toArray();

        dump('--- before products', $products);

        $firstOrderProductCount = 3;
        $secondOrderProductCount = 3;

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]
                        ,
                        [
                            'product_id' => $products[1]['_id'],
                            'count'      => $secondOrderProductCount,
                        ]
                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;
        $secondProductInventory = $products[1]['inventory'] - $secondOrderProductCount;
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => $secondProductInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[2]['_id'], 'inventory' => 20]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
                ['product_id' => $products[1]['_id'], 'name' => $products[1]['name'], 'count' => $secondOrderProductCount, 'price' => $products[1]['price']]
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

        ///////--------------------------------update order
        $updateFirstOrderProductCount = 5;
        $newOrderCount = 5;

        $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[0]['_id'],
                    'count'      => $updateFirstOrderProductCount
                ]
                ,
                [
                    'product_id' => $products[2]['_id'], //new product
                    'count'      => $newOrderCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'     => Auth::id(),
            'products'    => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $updateFirstOrderProductCount, 'price' => $products[0]['price']],
                ['product_id' => $products[2]['_id'], 'name' => $products[2]['name'], 'count' => $newOrderCount, 'price' => $products[2]['price']]
            ],
            'total_price' => ($updateFirstOrderProductCount * $products[0]['price']) + ($newOrderCount * $products[2]['price'])
        ]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => 20 - $updateFirstOrderProductCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => $secondProductInventory + $secondOrderProductCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[2]['_id'], 'inventory' => 20 - $newOrderCount]);

    }

    public function test_update_order_with_equals_and_new_product()
    {
        // update with equal => 3 = 3
        // update with new product => 5
        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(2)->create(['inventory' => 20])->toArray();

        $firstOrderProductCount = 3;

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]
                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);

        //second product
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => 20]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

        ///////--------------------------------update order
        $updateFirstOrderProductCount = 3; // 3 = 3
        $newOrderCount = 5;

        $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[0]['_id'],
                    'count'      => $updateFirstOrderProductCount
                ],
                [
                    'product_id' => $products[1]['_id'], //new product
                    'count'      => $newOrderCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $updateFirstOrderProductCount, 'price' => $products[0]['price']],
                ['product_id' => $products[1]['_id'], 'name' => $products[1]['name'], 'count' => $newOrderCount, 'price' => $products[1]['price']]
            ]
        ]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => (20 - $newOrderCount)]);
    }

    public function test_update_order_with_new_order()
    {
        // update with new order => old order must be removed (add to product inventory )

        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(2)->create(['inventory' => 20])->toArray();

        $firstOrderProductCount = 3;

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]
                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => 20]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

       ///////--------------------------------update order
        $newOrderCount = 3;
        $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[1]['_id'], //new product
                    'count'      => $newOrderCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[1]['_id'], 'name' => $products[1]['name'], 'count' => $newOrderCount, 'price' => $products[1]['price']]
            ]
        ]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory + $firstOrderProductCount]);
        $this->assertDatabaseHas(Product::class, ['_id' => $products[1]['_id'], 'inventory' => (20-$newOrderCount)]);
    }

    public function test_update_order_with_new_value_3_change_to_5_decrease_product_inventory()
    {
        // stored = 3 -->must change to 5
        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(1)->create(['inventory' => 20])->toArray();
        $firstOrderProductCount = 3;

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]
                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

        ///////--------------------------------update order
        $updateFirstOrderProductCount = 5;
        $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[0]['_id'],
                    'count'      => $updateFirstOrderProductCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'     => Auth::id(),
            'products'    => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $updateFirstOrderProductCount, 'price' => $products[0]['price']],
            ],
            'total_price' => $updateFirstOrderProductCount * $products[0]['price']
        ]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => 15]);
    }

    public function test_update_order_with_new_value_3_change_to_2_increase_product_inventory()
    {
        // stored = 3 -->must change to 2
        //////////////////---------------------------create orders
        $this->clearProducts();
        $products = Product::factory(1)->create(['inventory' => 20])->toArray();
        $firstOrderProductCount = 3;

        //create an order
        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'),
                [
                    'products' => [
                        [
                            'product_id' => $products[0]['_id'],
                            'count'      => $firstOrderProductCount,
                        ]
                    ]
                ]
            );

        $firstProductInventory = $products[0]['inventory'] - $firstOrderProductCount;
        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => $firstProductInventory]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'  => Auth::id(),
            'products' => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $firstOrderProductCount, 'price' => $products[0]['price']],
            ]
        ]);

        /** @var Order $order */
        $order = Order::query()->where('user_id', Auth::id())->first();

        ///////--------------------------------update order
        $updateFirstOrderProductCount = 2;
        $this->patchJson(route('orders.update', $order->_id), [
            'products' => [
                [
                    'product_id' => $products[0]['_id'],
                    'count'      => $updateFirstOrderProductCount
                ]
            ]
        ]);

        $this->assertDatabaseHas(Order::class, [
            'user_id'     => Auth::id(),
            'products'    => [
                ['product_id' => $products[0]['_id'], 'name' => $products[0]['name'], 'count' => $updateFirstOrderProductCount, 'price' => $products[0]['price']],
            ],
            'total_price' => $updateFirstOrderProductCount * $products[0]['price']
        ]);

        $this->assertDatabaseHas(Product::class, ['_id' => $products[0]['_id'], 'inventory' => 18]);
    }

}

