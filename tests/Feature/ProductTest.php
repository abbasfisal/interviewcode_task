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

    public function test_product_show()
    {
        $this->clearProducts();
        Product::factory(10)->create();

        /** @var Product $product */
        $product = Product::query()->first();

        $response = $this->getJson(route('products.show', $product->_id));

        $response->assertExactJson([
            'data'    => [
                'name'      => $product->name,
                'price'     => $product->price,
                'inventory' => $product->inventory
            ],
            'message' => "show"])
            ->assertStatus(200);

        $this->assertDatabaseCount(Product::class, 10);

    }

    public function test_update_product()
    {
        $this->clearProducts();
        $product = Product::factory(1)->create();

        $this->patchJson(route('products.update', [$product[0]->_id]), [
            'name' => 'my_product'
        ])
            ->assertExactJson([
                'data'    => [
                    'name'      => 'my_product',
                    'price'     => $product[0]->price,
                    'inventory' => $product[0]->inventory
                ],
                'message' => "update successfully"])
            ->assertStatus(200);


        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, ['name' => 'my_product']);
    }

}
