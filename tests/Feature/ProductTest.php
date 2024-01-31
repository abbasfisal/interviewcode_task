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
        $token = $this->login_user();
        Product::factory(10)->create();

        $response = $this
            ->withToken($token)
            ->getJson(route('products.index'));

        $this->assertDatabaseCount(Product::class, 10);

        $response->assertStatus(200);
    }

    public function test_store_validation()
    {
        $this->clearProducts();
        $token = $this->login_user();

        $response = $this
            ->withToken($token)
            ->postJson(route('products.store'));
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonMissingValidationErrors(['errors.name', 'errors.price', 'errors.inventory']);
    }

    public function login_user()
    {
        $data = [
            'name'                  => 'alireza',
            'email'                 => 'alireza@gmail.com',
            'password'              => 'password',
            'password_confirmation' => 'password'
        ];

        return $this->postJson(route('register'), $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ])->json('access_token');

    }

    public function test_store_product()
    {
        $this->clearProducts();
        $token = $this->login_user();
        $product_data = ['name' => 'product one', 'price' => 20000, 'inventory' => 200];

        $response = $this
            ->withToken($token)
            ->postJson(route('products.store'), $product_data);
        $response->assertStatus(201);
        $this->assertDatabaseCount(Product::class, 1);

    }

    public function test_product_show()
    {
        $this->clearProducts();
        $token = $this->login_user();
        Product::factory(10)->create();

        /** @var Product $product */
        $product = Product::query()->first();

        $response = $this
            ->withToken($token)
            ->getJson(route('products.show', $product->_id));

        $response->assertExactJson([
            'data'    => [
                'product_id' => $product->_id,
                'name'       => $product->name,
                'price'      => $product->price,
                'inventory'  => $product->inventory
            ],
            'message' => "show"])
            ->assertStatus(200);

        $this->assertDatabaseCount(Product::class, 10);

    }

    public function test_update_product()
    {
        $this->clearProducts();
        $token = $this->login_user();
        $product = Product::factory(1)->create();

        $this
            ->withToken($token)
            ->patchJson(route('products.update', [$product[0]->_id]), [
                'name' => 'my_product'
            ])
            ->assertExactJson([
                'data'    => [
                    'product_id' => $product[0]->_id,
                    'name'       => 'my_product',
                    'price'      => $product[0]->price,
                    'inventory'  => $product[0]->inventory
                ],
                'message' => "update successfully"])
            ->assertStatus(200);


        $this->assertDatabaseCount(Product::class, 1);
        $this->assertDatabaseHas(Product::class, ['name' => 'my_product']);
    }

    public function test_delete_product()
    {
        $this->clearProducts();
        $token = $this->login_user();
        $product = Product::factory(1)->create();

        $this
            ->withToken($token)
            ->deleteJson(route('products.destroy', [$product[0]->_id]))
            ->assertExactJson([
                'message' => "delete successfully"
            ])
            ->assertSuccessful();

        $this->assertSoftDeleted(Product::class, [
            'name'      => $product[0]->name,
            'price'     => $product[0]->price,
            'inventory' => $product[0]->inventory
        ]);
    }
}
