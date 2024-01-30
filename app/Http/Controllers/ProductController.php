<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repository\Product\ProductRepositoryInterface;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function __construct(private ProductRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = $this->repository->index();

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->repository->store($request->validated());
        return Response::json(
            [
                'messages' => 'created',
                'data'     => new ProductResource($product),
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
