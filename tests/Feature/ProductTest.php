<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    // use RefreshDatabase;
    public function clearProducts(): void
    {
        Product::query()->forceDelete();
    }

    public function test_index(): void
    {
        $this->clearProducts();
        Product::factory(10)->create();

        $response = $this->get('/api/products');

        $this->assertDatabaseCount(Product::class, 10);

        $response->assertStatus(200);
    }
}
