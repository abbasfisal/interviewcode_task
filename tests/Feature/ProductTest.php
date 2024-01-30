<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProductTest extends TestCase
{
    // use RefreshDatabase;
    public function clearProducts(): void
    {
        User::query()->forceDelete();
        Product::query()->forceDelete();
    }

    public function test_index(): void
    {
        $this->clearProducts();
        Product::factory(10)->create();

        $response = $this->get(route('products.index'));

        $this->assertDatabaseCount(Product::class, 10);

        $response->assertStatus(200);
    }

    public function test_store_validation()
    {
        $response = $this->postJson(route('products.store'));
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonMissingValidationErrors(['errors.name', 'errors.price', 'errors.inventory']);
    }

    public function test_store_product()
    {
        $this->clearProducts();
        $product_data = ['name' => 'product one', 'price' => 20000, 'inventory' => 200];

        $response = $this->postJson(route('products.store'), $product_data);
        $response->assertStatus(201);
        $this->assertDatabaseCount(Product::class, 1);

    }
}
